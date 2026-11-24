<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Settings\Services;

use App\Modules\Core\Models\CompanySetting;
use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Core\Services\Money;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function update(CompanySetting $settings, array $data, ?UploadedFile $logo = null): CompanySetting
    {
        return DB::transaction(function () use ($settings, $data, $logo) {
            $data['default_tax_rate'] = Money::percentToBasisPoints($data['default_tax_rate']);
            $data['currency_code'] = strtoupper($data['currency_code']);
            $data['quotation_prefix'] = strtoupper($data['quotation_prefix']);
            $data['invoice_prefix'] = strtoupper($data['invoice_prefix']);
            $data['payment_prefix'] = strtoupper($data['payment_prefix']);
            $data['expense_prefix'] = strtoupper($data['expense_prefix']);

            if ($logo) {
                if ($settings->logo_path) {
                    Storage::disk('public')->delete($settings->logo_path);
                }
                $data['logo_path'] = $this->storeSanitizedLogo($logo);
            }

            $settings->fill($data)->save();
            $changes = $this->activity->changedFields($settings);
            unset($changes['logo_path']);
            $this->activity->log('settings', 'updated', 'Company settings updated', $settings, $changes ?: null);

            return $settings->refresh();
        });
    }

    public function removeLogo(CompanySetting $settings): CompanySetting
    {
        if ($settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $settings->update(['logo_path' => null]);
            $this->activity->log('settings', 'updated', 'Company logo removed', $settings);
        }

        return $settings->refresh();
    }

    /**
     * Re-encode the uploaded logo through GD so any embedded metadata, colour
     * profiles or malformed chunks are discarded. Falls back to a normal store
     * when GD is unavailable; the request already allowlists real image types.
     */
    private function storeSanitizedLogo(UploadedFile $logo): string
    {
        $path = 'logos/'.Str::random(40).'.png';
        $encoded = $this->reencodeImage($logo->getRealPath());

        if ($encoded !== null) {
            Storage::disk('public')->put($path, $encoded);

            return $path;
        }

        return $logo->store('logos', 'public');
    }

    private function reencodeImage(string|false $source): ?string
    {
        if ($source === false || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $contents = @file_get_contents($source);
        if ($contents === false) {
            return null;
        }

        $image = @imagecreatefromstring($contents);
        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $max = 512;

        if ($width > $max || $height > $max) {
            $ratio = min($max / $width, $max / $height);
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        ob_start();
        imagepng($image);
        $encoded = ob_get_clean();
        imagedestroy($image);

        return is_string($encoded) && $encoded !== '' ? $encoded : null;
    }
}
