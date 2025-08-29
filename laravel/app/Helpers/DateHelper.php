<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Helper class for date formatting and operations.
 *
 * This class provides utilities for standardized date formatting
 * across the application, ensuring consistent date display formats
 * in emails, UI components, and other contexts.
 *
 * @package App\Helpers
 */
class DateHelper
{
    /**
     * Format a date with time for display contexts.
     *
     * Returns date in format: "Jan 15, 2024 at 2:30 PM"
     * Used for timestamps, dates with time information, etc.
     *
     * @param Carbon|null $date The date to format
     * @return string|null Formatted date string or null if date is null
     */
    public static function formatDateTime(?Carbon $date): ?string
    {
        if (!$date) {
            return null;
        }

        return $date->format('M d, Y \a\t h:i A');
    }

    /**
     * Format a date without time for simple date contexts.
     *
     * Returns date in format: "Jan 15, 2024"
     * Used for simple date displays, etc.
     *
     * @param Carbon|null $date The date to format
     * @return string|null Formatted date string or null if date is null
     */
    public static function formatDate(?Carbon $date): ?string
    {
        if (!$date) {
            return null;
        }

        return $date->format('M d, Y');
    }
}