<?php

namespace TerminalUI\Core;

use TerminalUI\Events\Event;

/**
 * Desktop (Window Manager)
 *
 * Like TDesktop in Turbo Vision - manages multiple windows,
 * z-ordering, focus, and event routing.
 *
 * Features:
 * - Window stacking (z-order)
 * - Focus management between windows
 * - Event routing to active window
 * - Background/wallpaper (optional)
 */
class Desktop extends View
{
    protected Application $app;
    protected ?Window $activeWindow = null;

    public function __construct(Application $app)
    {
        $this->app = $app;

        // Desktop is full terminal size
        // For now, assume 120x30 (will be dynamic in future)
        parent::__construct(
            Rect::make(0, 0, 120, 30),
            'Desktop'
        );
    }

    /**
     * Draw the desktop and all windows
     */
    public function draw(): void
    {
        // Draw background (optional - could render wallpaper, pattern, etc.)
        $this->drawBackground();

        // Draw all windows in z-order (bottom to top)
        $this->drawChildren();
    }

    /**
     * Draw desktop background
     */
    protected function drawBackground(): void
    {
        // For now, just clear with default background
        // Future: Could render ASCII art, patterns, colors, etc.

        // Already cleared by Application, but we could set a background color
        // echo "\033[44m"; // Blue background
        // echo $this->clear();
        // echo "\033[0m";
    }

    /**
     * Add a window to the desktop
     */
    public function add(View $child): self
    {
        parent::add($child);

        // If this is the first window, make it active
        if ($child instanceof Window && count($this->children) === 1) {
            $this->setActiveWindow($child);
        }

        return $this;
    }

    /**
     * Handle events - route to active window
     */
    public function handleEvent(Event $event): bool
    {
        // Active window gets first shot at handling
        if ($this->activeWindow && $this->activeWindow->handleEvent($event)) {
            return true;
        }

        // Desktop-level shortcuts
        // (Could implement Alt+# to switch windows, etc.)

        return false;
    }

    /**
     * Set the active (focused) window
     */
    public function setActiveWindow(Window $window): void
    {
        // Blur current active window
        if ($this->activeWindow) {
            $this->activeWindow->blur();
        }

        $this->activeWindow = $window;

        // Move window to top of z-order
        $this->bringToFront($window);

        // Focus new active window
        $window->focus();

        // Request redraw
        $this->app->requestRedraw();
    }

    /**
     * Bring window to front (top of z-order)
     */
    protected function bringToFront(Window $window): void
    {
        // Remove from current position
        $this->children = array_filter(
            $this->children,
            fn($child) => $child !== $window
        );

        // Add to end (top of stack)
        $this->children[] = $window;

        // Re-index array
        $this->children = array_values($this->children);
    }

    /**
     * Get active window
     */
    public function getActiveWindow(): ?Window
    {
        return $this->activeWindow;
    }

    /**
     * Get all windows
     */
    public function getWindows(): array
    {
        return array_filter(
            $this->children,
            fn($child) => $child instanceof Window
        );
    }

    /**
     * Close active window
     */
    public function closeActiveWindow(): void
    {
        if ($this->activeWindow) {
            $this->activeWindow->close();
            $this->remove($this->activeWindow);

            // Activate previous window if any
            $windows = $this->getWindows();
            if (count($windows) > 0) {
                $this->setActiveWindow(end($windows));
            } else {
                $this->activeWindow = null;
            }
        }
    }

    /**
     * Cycle to next window (Alt+Tab style)
     */
    public function nextWindow(): void
    {
        $windows = $this->getWindows();

        if (count($windows) <= 1) {
            return;
        }

        $currentIndex = array_search($this->activeWindow, $windows, true);

        if ($currentIndex === false) {
            $this->setActiveWindow($windows[0]);
            return;
        }

        $nextIndex = ($currentIndex + 1) % count($windows);
        $this->setActiveWindow($windows[$nextIndex]);
    }

    /**
     * Cycle to previous window
     */
    public function prevWindow(): void
    {
        $windows = $this->getWindows();

        if (count($windows) <= 1) {
            return;
        }

        $currentIndex = array_search($this->activeWindow, $windows, true);

        if ($currentIndex === false) {
            $this->setActiveWindow($windows[0]);
            return;
        }

        $prevIndex = ($currentIndex - 1 + count($windows)) % count($windows);
        $this->setActiveWindow($windows[$prevIndex]);
    }

    /**
     * Close all windows
     */
    public function closeAllWindows(): void
    {
        foreach ($this->getWindows() as $window) {
            $window->close();
            $this->remove($window);
        }

        $this->activeWindow = null;
    }

    /**
     * Cascade windows (arrange in staggered pattern)
     */
    public function cascade(): void
    {
        $windows = $this->getWindows();
        $offset = 0;

        foreach ($windows as $window) {
            $window->setBounds(
                Rect::make(
                    5 + $offset,
                    2 + $offset,
                    60,
                    20
                )
            );

            $offset += 3;
        }

        $this->app->requestRedraw();
    }

    /**
     * Tile windows horizontally
     */
    public function tileHorizontal(): void
    {
        $windows = $this->getWindows();
        $count = count($windows);

        if ($count === 0) {
            return;
        }

        $windowHeight = (int) ($this->bounds->height / $count);

        foreach ($windows as $i => $window) {
            $window->setBounds(
                Rect::make(
                    0,
                    $i * $windowHeight,
                    $this->bounds->width,
                    $windowHeight
                )
            );
        }

        $this->app->requestRedraw();
    }

    /**
     * Tile windows vertically
     */
    public function tileVertical(): void
    {
        $windows = $this->getWindows();
        $count = count($windows);

        if ($count === 0) {
            return;
        }

        $windowWidth = (int) ($this->bounds->width / $count);

        foreach ($windows as $i => $window) {
            $window->setBounds(
                Rect::make(
                    $i * $windowWidth,
                    0,
                    $windowWidth,
                    $this->bounds->height
                )
            );
        }

        $this->app->requestRedraw();
    }
}
