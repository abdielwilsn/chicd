/**
 * Formats a number of minutes as hours and minutes, for example "7h 05m".
 */
export function formatMinutes(minutes: number): string {
    const hours = Math.floor(minutes / 60);
    const remainder = String(minutes % 60).padStart(2, '0');

    return hours > 0 ? `${hours}h ${remainder}m` : `${minutes % 60}m`;
}
