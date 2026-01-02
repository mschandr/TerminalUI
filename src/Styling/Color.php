<?php

namespace TerminalUI\Styling;

/**
 * ANSI Color Support for Terminal UI
 *
 * Supports both 16-color and 256-color modes
 */
enum Color: string
{
    // Basic 16 colors
    case BLACK = 'black';
    case RED = 'red';
    case GREEN = 'green';
    case YELLOW = 'yellow';
    case BLUE = 'blue';
    case MAGENTA = 'magenta';
    case CYAN = 'cyan';
    case WHITE = 'white';

    // Bright variants
    case BRIGHT_BLACK = 'bright_black';
    case BRIGHT_RED = 'bright_red';
    case BRIGHT_GREEN = 'bright_green';
    case BRIGHT_YELLOW = 'bright_yellow';
    case BRIGHT_BLUE = 'bright_blue';
    case BRIGHT_MAGENTA = 'bright_magenta';
    case BRIGHT_CYAN = 'bright_cyan';
    case BRIGHT_WHITE = 'bright_white';

    // Grays (256-color mode)
    case GRAY_DARK = 'gray_dark';
    case GRAY = 'gray';
    case GRAY_LIGHT = 'gray_light';

    /**
     * Get ANSI foreground color code
     */
    public function toForegroundCode(): string
    {
        return match($this) {
            self::BLACK => "\033[30m",
            self::RED => "\033[31m",
            self::GREEN => "\033[32m",
            self::YELLOW => "\033[33m",
            self::BLUE => "\033[34m",
            self::MAGENTA => "\033[35m",
            self::CYAN => "\033[36m",
            self::WHITE => "\033[37m",

            self::BRIGHT_BLACK => "\033[90m",
            self::BRIGHT_RED => "\033[91m",
            self::BRIGHT_GREEN => "\033[92m",
            self::BRIGHT_YELLOW => "\033[93m",
            self::BRIGHT_BLUE => "\033[94m",
            self::BRIGHT_MAGENTA => "\033[95m",
            self::BRIGHT_CYAN => "\033[96m",
            self::BRIGHT_WHITE => "\033[97m",

            // 256-color grays
            self::GRAY_DARK => "\033[38;5;235m",
            self::GRAY => "\033[38;5;245m",
            self::GRAY_LIGHT => "\033[38;5;250m",
        };
    }

    /**
     * Get ANSI background color code
     */
    public function toBackgroundCode(): string
    {
        return match($this) {
            self::BLACK => "\033[40m",
            self::RED => "\033[41m",
            self::GREEN => "\033[42m",
            self::YELLOW => "\033[43m",
            self::BLUE => "\033[44m",
            self::MAGENTA => "\033[45m",
            self::CYAN => "\033[46m",
            self::WHITE => "\033[47m",

            self::BRIGHT_BLACK => "\033[100m",
            self::BRIGHT_RED => "\033[101m",
            self::BRIGHT_GREEN => "\033[102m",
            self::BRIGHT_YELLOW => "\033[103m",
            self::BRIGHT_BLUE => "\033[104m",
            self::BRIGHT_MAGENTA => "\033[105m",
            self::BRIGHT_CYAN => "\033[106m",
            self::BRIGHT_WHITE => "\033[107m",

            // 256-color grays
            self::GRAY_DARK => "\033[48;5;235m",
            self::GRAY => "\033[48;5;245m",
            self::GRAY_LIGHT => "\033[48;5;250m",
        };
    }

    /**
     * Create color from RGB (256-color mode)
     */
    public static function rgb(int $r, int $g, int $b): string
    {
        // Convert RGB to 256-color palette
        $r = (int) ($r / 255 * 5);
        $g = (int) ($g / 255 * 5);
        $b = (int) ($b / 255 * 5);

        $code = 16 + (36 * $r) + (6 * $g) + $b;

        return "\033[38;5;{$code}m";
    }

    /**
     * Create background from RGB
     */
    public static function rgbBackground(int $r, int $g, int $b): string
    {
        $r = (int) ($r / 255 * 5);
        $g = (int) ($g / 255 * 5);
        $b = (int) ($b / 255 * 5);

        $code = 16 + (36 * $r) + (6 * $g) + $b;

        return "\033[48;5;{$code}m";
    }

    /**
     * From hex color
     */
    public static function hex(string $hex): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return self::rgb($r, $g, $b);
    }
}
