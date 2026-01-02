<?php

namespace TerminalUI\Components;

use TerminalUI\Core\{View, Rect};
use TerminalUI\Styling\StyleSheet;
use TerminalUI\Events\Event;

/**
 * Label Component
 *
 * Simple text display component
 *
 * Features:
 * - Single or multi-line text
 * - Text alignment (left, center, right)
 * - Word wrapping
 * - Color and styling
 */
class Label extends View
{
    protected string $text;

    public function __construct(string $text, Rect|StyleSheet $boundsOrStyle, ?string $name = null)
    {
        parent::__construct($boundsOrStyle, $name);
        $this->text = $text;
    }

    /**
     * Draw the label
     */
    public function draw(): void
    {
        if (!$this->visible) {
            return;
        }

        $pos = $this->getAbsolutePosition();
        $lines = $this->wrapText($this->text, $this->bounds->width);

        // Get text alignment
        $align = $this->style->has('text-align') ? $this->style->get('text-align') : 'left';

        // Apply colors
        $output = '';
        if ($this->style->has('foreground')) {
            $output .= $this->style->get('foreground')->toForegroundCode();
        }
        if ($this->style->has('background')) {
            $output .= $this->style->get('background')->toBackgroundCode();
        }

        // Apply font weight (bold)
        if ($this->style->has('font-weight') && $this->style->get('font-weight') === 'bold') {
            $output .= "\033[1m";
        }

        // Draw each line
        foreach ($lines as $i => $line) {
            if ($i >= $this->bounds->height) {
                break; // Don't overflow height
            }

            $output .= $this->moveCursor($pos['x'], $pos['y'] + $i);
            $output .= $this->alignText($line, $align, $this->bounds->width);
        }

        // Reset styles
        $output .= "\033[0m";

        echo $output;

        // Draw children (if any)
        $this->drawChildren();
    }

    /**
     * Align text based on alignment setting
     */
    protected function alignText(string $text, string $align, int $width): string
    {
        $textLen = mb_strlen($text);

        return match($align) {
            'center' => str_repeat(' ', (int)(($width - $textLen) / 2)) . $text,
            'right' => str_repeat(' ', $width - $textLen) . $text,
            default => $text // left
        };
    }

    /**
     * Wrap text to fit width
     */
    protected function wrapText(string $text, int $width): array
    {
        // Split by newlines first
        $paragraphs = explode("\n", $text);
        $lines = [];

        foreach ($paragraphs as $paragraph) {
            if (mb_strlen($paragraph) <= $width) {
                $lines[] = $paragraph;
                continue;
            }

            // Word wrap
            $words = explode(' ', $paragraph);
            $currentLine = '';

            foreach ($words as $word) {
                if (mb_strlen($currentLine . ' ' . $word) <= $width) {
                    $currentLine .= ($currentLine ? ' ' : '') . $word;
                } else {
                    if ($currentLine) {
                        $lines[] = $currentLine;
                    }
                    $currentLine = $word;
                }
            }

            if ($currentLine) {
                $lines[] = $currentLine;
            }
        }

        return $lines;
    }

    /**
     * Handle events (labels don't handle events by default)
     */
    public function handleEvent(Event $event): bool
    {
        return false;
    }

    /**
     * Set label text
     */
    public function setText(string $text): self
    {
        $this->text = $text;
        $this->invalidate();
        return $this;
    }

    /**
     * Get label text
     */
    public function getText(): string
    {
        return $this->text;
    }
}
