export type VehicleCollection = 'blomwheels-saved' | 'blomwheels-compare';

export function readVehicleIds(key: VehicleCollection): number[] {
    if (typeof window === 'undefined') return [];
    try {
        const data: unknown = JSON.parse(window.localStorage.getItem(key) ?? '[]');
        return Array.isArray(data) ? [...new Set(data.filter((v): v is number => Number.isSafeInteger(v) && v > 0))] : [];
    } catch { return []; }
}

export function toggleVehicleId(key: VehicleCollection, id: number, limit = 30): number[] {
    const current = readVehicleIds(key);
    const next = current.includes(id) ? current.filter(v => v !== id) : [...current, id].slice(-limit);
    if (typeof window !== 'undefined') {
        window.localStorage.setItem(key, JSON.stringify(next));
        window.dispatchEvent(new Event('blomwheels-list-changed'));
    }
    return next;
}

export function vehicleCollectionUrl(kind: 'compare' | 'saved'): string {
    const key: VehicleCollection = kind === 'compare' ? 'blomwheels-compare' : 'blomwheels-saved';
    const ids = readVehicleIds(key);
    return '/automobiles/' + kind + (ids.length ? '?ids=' + ids.join(',') : '');
}
