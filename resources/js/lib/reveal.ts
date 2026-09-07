/**
 * Reveal-on-scroll action.
 *
 * The element starts untouched, so a page with JS disabled — or a reader who
 * asked for reduced motion — sees the content immediately. The action adds
 * the hidden state itself, then flips it once the element scrolls into view.
 *
 * ponytail: IntersectionObserver + a CSS transition instead of an animation
 * library. Ceiling: no scroll-linked scrubbing; reach for a library only if a
 * design needs progress-tied motion.
 */

export type RevealOptions = {
    /** Milliseconds to wait after the element enters view. Use for stagger. */
    delay?: number;
    /** Travel distance in pixels. */
    y?: number;
};

const prefersReducedMotion = (): boolean =>
    typeof window !== 'undefined' &&
    window.matchMedia?.('(prefers-reduced-motion: reduce)').matches === true;

export function reveal(node: HTMLElement, options: RevealOptions = {}) {
    if (prefersReducedMotion() || typeof IntersectionObserver === 'undefined') {
        return {};
    }

    const apply = (settings: RevealOptions): void => {
        node.style.setProperty('--reveal-y', `${settings.y ?? 16}px`);
        node.style.setProperty('--reveal-delay', `${settings.delay ?? 0}ms`);
    };

    apply(options);
    node.dataset.reveal = 'hidden';

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    node.dataset.reveal = 'shown';
                    observer.unobserve(node);
                }
            }
        },
        // Fire a little before the element reaches the fold.
        { rootMargin: '0px 0px -10% 0px', threshold: 0.05 },
    );

    observer.observe(node);

    return {
        update(next: RevealOptions = {}) {
            apply(next);
        },
        destroy() {
            observer.disconnect();
        },
    };
}
