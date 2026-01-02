<?php

namespace TerminalUI\Events;

/**
 * Keyboard Event
 *
 * Represents a key press in the terminal
 *
 * Special Keys:
 * - "\e", "\x1b" => ESC
 * - "\t" => Tab
 * - "\n", "\r" => Enter
 * - "\x7f" => Backspace
 * - Arrow keys are escape sequences: "\e[A", "\e[B", "\e[C", "\e[D"
 */
class KeyEvent extends Event
{
    public readonly string $key;
    public readonly string $raw;

    // Special key constants
    public const ESC = "\x1b";
    public const TAB = "\t";
    public const ENTER = "\n";
    public const BACKSPACE = "\x7f";
    public const ARROW_UP = "\e[A";
    public const ARROW_DOWN = "\e[B";
    public const ARROW_RIGHT = "\e[C";
    public const ARROW_LEFT = "\e[D";
    public const HOME = "\e[H";
    public const END = "\e[F";
    public const PAGE_UP = "\e[5~";
    public const PAGE_DOWN = "\e[6~";
    public const DELETE = "\e[3~";

    // Function keys
    public const F1 = "\eOP";
    public const F2 = "\eOQ";
    public const F3 = "\eOR";
    public const F4 = "\eOS";
    public const F5 = "\e[15~";
    public const F6 = "\e[17~";
    public const F7 = "\e[18~";
    public const F8 = "\e[19~";
    public const F9 = "\e[20~";
    public const F10 = "\e[21~";
    public const F11 = "\e[23~";
    public const F12 = "\e[24~";

    public function __construct(string $key)
    {
        $this->raw = $key;
        $this->key = $this->normalizeKey($key);
    }

    /**
     * Normalize special keys for easier matching
     */
    private function normalizeKey(string $key): string
    {
        // ESC can be \e or \x1b
        if ($key === "\e" || $key === "\x1b") {
            return self::ESC;
        }

        // Enter can be \n or \r
        if ($key === "\n" || $key === "\r") {
            return self::ENTER;
        }

        return $key;
    }

    /**
     * Check if this is a printable character
     */
    public function isPrintable(): bool
    {
        // Printable ASCII range (32-126)
        if (strlen($this->key) === 1) {
            $ord = ord($this->key);
            return $ord >= 32 && $ord <= 126;
        }

        return false;
    }

    /**
     * Check if this is an arrow key
     */
    public function isArrowKey(): bool
    {
        return in_array($this->key, [
            self::ARROW_UP,
            self::ARROW_DOWN,
            self::ARROW_LEFT,
            self::ARROW_RIGHT
        ], true);
    }

    /**
     * Check if this is a function key
     */
    public function isFunctionKey(): bool
    {
        return in_array($this->key, [
            self::F1, self::F2, self::F3, self::F4,
            self::F5, self::F6, self::F7, self::F8,
            self::F9, self::F10, self::F11, self::F12
        ], true);
    }

    /**
     * Get human-readable key name
     */
    public function getKeyName(): string
    {
        return match($this->key) {
            self::ESC => 'ESC',
            self::TAB => 'TAB',
            self::ENTER => 'ENTER',
            self::BACKSPACE => 'BACKSPACE',
            self::ARROW_UP => 'UP',
            self::ARROW_DOWN => 'DOWN',
            self::ARROW_LEFT => 'LEFT',
            self::ARROW_RIGHT => 'RIGHT',
            self::HOME => 'HOME',
            self::END => 'END',
            self::PAGE_UP => 'PAGE_UP',
            self::PAGE_DOWN => 'PAGE_DOWN',
            self::DELETE => 'DELETE',
            self::F1 => 'F1',
            self::F2 => 'F2',
            self::F3 => 'F3',
            self::F4 => 'F4',
            self::F5 => 'F5',
            self::F6 => 'F6',
            self::F7 => 'F7',
            self::F8 => 'F8',
            self::F9 => 'F9',
            self::F10 => 'F10',
            self::F11 => 'F11',
            self::F12 => 'F12',
            default => $this->isPrintable() ? $this->key : 'UNKNOWN'
        };
    }

    /**
     * Check if modifier key is pressed (Ctrl+key)
     */
    public function isCtrl(): bool
    {
        if (strlen($this->key) === 1) {
            $ord = ord($this->key);
            // Ctrl+A through Ctrl+Z are 1-26
            return $ord >= 1 && $ord <= 26;
        }

        return false;
    }

    /**
     * Get the character if this is a Ctrl+key combination
     */
    public function getCtrlChar(): ?string
    {
        if ($this->isCtrl()) {
            // Convert Ctrl+A (1) to 'a', Ctrl+B (2) to 'b', etc.
            return chr(ord($this->key) + 96);
        }

        return null;
    }
}
