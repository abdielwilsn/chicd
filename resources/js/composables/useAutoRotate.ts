import type { Ref } from 'vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Slides that take turns on a timer, for the home page hero. Rotation stops
 * for visitors who prefer reduced motion and can be paused and resumed. A
 * pause by the visitor sticks; a temporary hold (hovering over or focusing
 * inside the slides) only lasts while it is in place.
 */
export function useAutoRotate(count: Ref<number>, intervalMs = 7000) {
    const current = ref(0);
    const isPlaying = ref(true);
    const isHeld = ref(false);
    let timer: ReturnType<typeof setInterval> | undefined;

    const hasSeveral = computed(() => count.value > 1);

    function stopTimer(): void {
        clearInterval(timer);
        timer = undefined;
    }

    function startTimer(): void {
        stopTimer();

        if (!hasSeveral.value || !isPlaying.value || isHeld.value) {
            return;
        }

        timer = setInterval(() => {
            current.value = (current.value + 1) % count.value;
        }, intervalMs);
    }

    /** Show a slide and restart the timer, so it gets its full time. */
    function goTo(index: number): void {
        current.value = (index + count.value) % count.value;
        startTimer();
    }

    function next(): void {
        goTo(current.value + 1);
    }

    function previous(): void {
        goTo(current.value - 1);
    }

    function togglePlaying(): void {
        isPlaying.value = !isPlaying.value;
        startTimer();
    }

    /** Hold the current slide while the visitor is reading or using it. */
    function hold(isHolding: boolean): void {
        isHeld.value = isHolding;
        startTimer();
    }

    onMounted(() => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            isPlaying.value = false;
        }

        startTimer();
    });

    onBeforeUnmount(stopTimer);

    return {
        current,
        isPlaying,
        hasSeveral,
        goTo,
        next,
        previous,
        togglePlaying,
        hold,
    };
}
