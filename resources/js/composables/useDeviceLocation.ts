import { ref } from 'vue';
import type { Ref } from 'vue';

export type DeviceLocation = {
    latitude: number;
    longitude: number;
    accuracy: number;
};

export type UseDeviceLocationReturn = {
    locating: Ref<boolean>;
    locationError: Ref<string | null>;
    readLocation: () => Promise<DeviceLocation | null>;
};

/**
 * Reads the device location once, when a staff member presses Clock In,
 * Clock Out, Start or End.
 *
 * This deliberately uses getCurrentPosition and never watchPosition: the
 * portal must not track staff continuously.
 */
export function useDeviceLocation(): UseDeviceLocationReturn {
    const locating = ref(false);
    const locationError = ref<string | null>(null);

    function messageFor(error: GeolocationPositionError): string {
        if (error.code === error.PERMISSION_DENIED) {
            return 'Location access is blocked. Allow location for this site in your browser settings, then try again.';
        }

        if (error.code === error.TIMEOUT) {
            return 'Finding your location took too long. Move closer to a window or outdoors and try again.';
        }

        return 'We could not read your location. Please try again.';
    }

    function readLocation(): Promise<DeviceLocation | null> {
        locationError.value = null;

        if (!('geolocation' in navigator)) {
            locationError.value =
                'This browser cannot share its location. Please use a phone or a different browser.';

            return Promise.resolve(null);
        }

        locating.value = true;

        return new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    locating.value = false;
                    resolve({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                    });
                },
                (error) => {
                    locating.value = false;
                    locationError.value = messageFor(error);
                    resolve(null);
                },
                { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 },
            );
        });
    }

    return { locating, locationError, readLocation };
}
