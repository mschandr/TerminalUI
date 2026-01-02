<?php

namespace TerminalUI\Layout;

/**
 * Customizable Border System
 *
 * Define each border character individually:
 *
 * $border = Border::create()
 *     ->top('═')
 *     ->bottom('═')
 *     ->left('║')
 *     ->right('║')
 *     ->topLeft('╔')
 *     ->topRight('╗')
 *     ->bottomLeft('╚')
 *     ->bottomRight('╝');
 *
 * Or use presets:
 * - Border::single()
 * - Border::double()
 * - Border::rounded()
 * - Border::thick()
 * - Border::ascii()
 */
class Border
{
    protected string $top = '─';
    protected string $bottom = '─';
    protected string $left = '│';
    protected string $right = '│';
    protected string $topLeft = '┌';
    protected string $topRight = '┐';
    protected string $bottomLeft = '└';
    protected string $bottomRight = '┘';

    public static function create(): self
    {
        return new self();
    }

    // ==================== Presets ====================

    public static function single(): self
    {
        return (new self())
            ->top('─')->bottom('─')
            ->left('│')->right('│')
            ->topLeft('┌')->topRight('┐')
            ->bottomLeft('└')->bottomRight('┘');
    }

    public static function double(): self
    {
        return (new self())
            ->top('═')->bottom('═')
            ->left('║')->right('║')
            ->topLeft('╔')->topRight('╗')
            ->bottomLeft('╚')->bottomRight('╝');
    }

    public static function rounded(): self
    {
        return (new self())
            ->top('─')->bottom('─')
            ->left('│')->right('│')
            ->topLeft('╭')->topRight('╮')
            ->bottomLeft('╰')->bottomRight('╯');
    }

    public static function thick(): self
    {
        return (new self())
            ->top('━')->bottom('━')
            ->left('┃')->right('┃')
            ->topLeft('┏')->topRight('┓')
            ->bottomLeft('┗')->bottomRight('┛');
    }

    public static function ascii(): self
    {
        return (new self())
            ->top('-')->bottom('-')
            ->left('|')->right('|')
            ->topLeft('+')->topRight('+')
            ->bottomLeft('+')->bottomRight('+');
    }

    public static function dots(): self
    {
        return (new self())
            ->top('·')->bottom('·')
            ->left('·')->right('·')
            ->topLeft('·')->topRight('·')
            ->bottomLeft('·')->bottomRight('·');
    }

    public static function stars(): self
    {
        return (new self())
            ->top('*')->bottom('*')
            ->left('*')->right('*')
            ->topLeft('*')->topRight('*')
            ->bottomLeft('*')->bottomRight('*');
    }

    public static function none(): self
    {
        return (new self())
            ->top(' ')->bottom(' ')
            ->left(' ')->right(' ')
            ->topLeft(' ')->topRight(' ')
            ->bottomLeft(' ')->bottomRight(' ');
    }

    // ==================== Setters (Fluent Interface) ====================

    public function top(string $char): self
    {
        $this->top = $char;
        return $this;
    }

    public function bottom(string $char): self
    {
        $this->bottom = $char;
        return $this;
    }

    public function left(string $char): self
    {
        $this->left = $char;
        return $this;
    }

    public function right(string $char): self
    {
        $this->right = $char;
        return $this;
    }

    public function topLeft(string $char): self
    {
        $this->topLeft = $char;
        return $this;
    }

    public function topRight(string $char): self
    {
        $this->topRight = $char;
        return $this;
    }

    public function bottomLeft(string $char): self
    {
        $this->bottomLeft = $char;
        return $this;
    }

    public function bottomRight(string $char): self
    {
        $this->bottomRight = $char;
        return $this;
    }

    /**
     * Set all characters at once
     */
    public function all(
        string $top,
        string $right,
        string $bottom,
        string $left,
        string $topLeft,
        string $topRight,
        string $bottomRight,
        string $bottomLeft
    ): self {
        $this->top = $top;
        $this->right = $right;
        $this->bottom = $bottom;
        $this->left = $left;
        $this->topLeft = $topLeft;
        $this->topRight = $topRight;
        $this->bottomRight = $bottomRight;
        $this->bottomLeft = $bottomLeft;
        return $this;
    }

    // ==================== Getters ====================

    public function getTop(): string { return $this->top; }
    public function getBottom(): string { return $this->bottom; }
    public function getLeft(): string { return $this->left; }
    public function getRight(): string { return $this->right; }
    public function getTopLeft(): string { return $this->topLeft; }
    public function getTopRight(): string { return $this->topRight; }
    public function getBottomLeft(): string { return $this->bottomLeft; }
    public function getBottomRight(): string { return $this->bottomRight; }

    // ==================== Rendering ====================

    /**
     * Draw a box using this border
     *
     * @param int $x X position
     * @param int $y Y position
     * @param int $width Box width
     * @param int $height Box height
     * @param string $title Optional title
     */
    public function draw(int $x, int $y, int $width, int $height, string $title = ''): string
    {
        $output = '';

        // Top border
        $output .= $this->moveCursor($x, $y);
        $output .= $this->topLeft;

        if ($title) {
            $titleLen = mb_strlen($title);
            $leftPadding = (int) (($width - 2 - $titleLen - 2) / 2); // -2 for corners, -2 for spaces around title
            $output .= str_repeat($this->top, max(1, $leftPadding));
            $output .= " {$title} ";
            $output .= str_repeat($this->top, max(1, $width - 2 - $leftPadding - $titleLen - 2));
        } else {
            $output .= str_repeat($this->top, $width - 2);
        }

        $output .= $this->topRight;

        // Side borders
        for ($row = 1; $row < $height - 1; $row++) {
            $output .= $this->moveCursor($x, $y + $row);
            $output .= $this->left;
            $output .= str_repeat(' ', $width - 2);
            $output .= $this->right;
        }

        // Bottom border
        $output .= $this->moveCursor($x, $y + $height - 1);
        $output .= $this->bottomLeft;
        $output .= str_repeat($this->bottom, $width - 2);
        $output .= $this->bottomRight;

        return $output;
    }

    /**
     * ANSI escape code to move cursor
     */
    private function moveCursor(int $x, int $y): string
    {
        return "\033[{$y};{$x}H";
    }

    /**
     * Create a border from a style name
     */
    public static function fromStyle(string $style): self
    {
        return match(strtolower($style)) {
            'single' => self::single(),
            'double' => self::double(),
            'rounded' => self::rounded(),
            'thick' => self::thick(),
            'ascii' => self::ascii(),
            'dots' => self::dots(),
            'stars' => self::stars(),
            'none' => self::none(),
            default => self::single(),
        };
    }
}
