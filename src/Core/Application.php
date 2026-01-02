<?php

namespace TerminalUI\Core;

use TerminalUI\Events\{Event, KeyEvent};

/**
 * Terminal Application (TUI Event Loop)
 *
 * Like TApplication in Turbo Vision - manages the main event loop,
 * terminal state, and window coordination.
 *
 * Features:
 * - Non-blocking keyboard input
 * - Terminal initialization/cleanup
 * - Signal handling (Ctrl+C, etc.)
 * - Event distribution to windows
 * - Screen refresh management
 */
class Application
{
    protected Desktop $desktop;
    protected bool $running = false;
    protected bool $needsRedraw = true;

    // Terminal state
    protected ?string $originalStty = null;
    protected bool $rawMode = false;

    // Event handling
    protected int $frameDelay = 16667; // ~60 FPS (microseconds)

    public function __construct()
    {
        $this->desktop = new Desktop($this);
    }

    /**
     * Run the application
     */
    public function run(): int
    {
        $this->initialize();

        try {
            $this->running = true;

            while ($this->running) {
                $this->processFrame();
                usleep($this->frameDelay);
            }

            return 0;
        } finally {
            $this->cleanup();
        }
    }

    /**
     * Process single frame of event loop
     */
    protected function processFrame(): void
    {
        // Handle input events
        if ($key = $this->readKey()) {
            $event = new KeyEvent($key);
            $this->handleEvent($event);
        }

        // Redraw if needed
        if ($this->needsRedraw) {
            $this->draw();
            $this->needsRedraw = false;
        }
    }

    /**
     * Initialize terminal for TUI mode
     */
    protected function initialize(): void
    {
        // Save original terminal state
        $this->originalStty = shell_exec('stty -g');

        // Enter raw mode (disable line buffering, echo)
        system('stty -icanon -echo');
        $this->rawMode = true;

        // Clear screen
        echo "\033[2J";

        // Hide cursor
        echo "\033[?25l";

        // Move cursor to home
        echo "\033[H";

        // Set up signal handlers
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        }

        // Enable mouse tracking (optional - for future)
        // echo "\033[?1000h";
    }

    /**
     * Clean up and restore terminal
     */
    protected function cleanup(): void
    {
        // Restore terminal state
        if ($this->rawMode && $this->originalStty) {
            system("stty {$this->originalStty}");
            $this->rawMode = false;
        }

        // Show cursor
        echo "\033[?25h";

        // Disable mouse tracking
        // echo "\033[?1000l";

        // Move cursor to bottom and clear
        echo "\033[999;1H";
        echo "\033[K";

        // Reset colors
        echo "\033[0m";
    }

    /**
     * Read single key from stdin (non-blocking)
     */
    protected function readKey(): ?string
    {
        // Set stdin to non-blocking
        stream_set_blocking(STDIN, false);

        $key = fread(STDIN, 16); // Read up to 16 bytes (for escape sequences)

        // Restore blocking mode
        stream_set_blocking(STDIN, true);

        return $key !== false && strlen($key) > 0 ? $key : null;
    }

    /**
     * Handle signal (Ctrl+C, kill, etc.)
     */
    public function handleSignal(int $signal): void
    {
        if ($signal === SIGINT || $signal === SIGTERM) {
            $this->quit();
        }
    }

    /**
     * Handle an event
     */
    public function handleEvent(Event $event): bool
    {
        // Let desktop (and its windows) handle first
        if ($this->desktop->handleEvent($event)) {
            return true;
        }

        // Application-level event handling
        if ($event instanceof KeyEvent) {
            // Global shortcuts
            if ($event->key === "\x03") { // Ctrl+C
                $this->quit();
                return true;
            }

            if ($event->key === "\x04") { // Ctrl+D
                $this->quit();
                return true;
            }
        }

        return false;
    }

    /**
     * Draw the entire application
     */
    protected function draw(): void
    {
        // Clear screen
        echo "\033[2J";
        echo "\033[H";

        // Draw desktop (which draws all windows)
        $this->desktop->draw();

        // Flush output
        flush();
    }

    /**
     * Request redraw on next frame
     */
    public function requestRedraw(): void
    {
        $this->needsRedraw = true;
    }

    /**
     * Quit the application
     */
    public function quit(): void
    {
        $this->running = false;
    }

    /**
     * Get desktop instance
     */
    public function getDesktop(): Desktop
    {
        return $this->desktop;
    }

    /**
     * Add a window to the desktop
     */
    public function addWindow(Window $window): self
    {
        $this->desktop->add($window);
        $this->requestRedraw();
        return $this;
    }

    /**
     * Remove a window from the desktop
     */
    public function removeWindow(Window $window): self
    {
        $this->desktop->remove($window);
        $this->requestRedraw();
        return $this;
    }

    /**
     * Set frame rate (FPS)
     */
    public function setFrameRate(int $fps): self
    {
        $this->frameDelay = (int) (1000000 / $fps);
        return $this;
    }

    /**
     * Check if application is running
     */
    public function isRunning(): bool
    {
        return $this->running;
    }
}
