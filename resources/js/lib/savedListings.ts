import { readVehicleIds, toggleVehicleId } from '@/lib/vehicleCollections';

export type SavedType = 'lands' | 'animals' | 'products' | 'vehicles';
const keys: Record<Exclude<SavedType, 'vehicles'>, string> = {
    lands: 'blomfree-saved-lands',
    animals: 'blomfree-saved-animals',
    products: 'blomfree-saved-products',
};

export function getSavedIds(type: SavedType): number[] {
    if (type === 'vehicles') return readVehicleIds('blomwheels-saved');
    if (typeof window === 'undefined') return [];
    try {
        const stored: unknown = JSON.parse(window.localStorage.getItem(keys[type]) ?? '[]');
        if (!Array.isArray(stored)) return [];
        return Array.from(new Set(stored.filter((x): x is number => Number.isSafeInteger(x) && x > 0))).slice(0, 30);
    } catch { return []; }
}
export function toggleSavedListing(type: SavedType, id: number): boolean {
    if (typeof window === 'undefined' || !Number.isSafeInteger(id) || id < 1) return false;
    if (type === 'vehicles') {
        const result = toggleVehicleId('blomwheels-saved', id);
        window.dispatchEvent(new Event('blomfree-saved-changed'));
        return result.includes(id);
    }
    const current = getSavedIds(type);
    const next = current.includes(id) ? current.filter(x => x !== id) : [...current, id].slice(-30);
    window.localStorage.setItem(keys[type], JSON.stringify(next));
    window.dispatchEvent(new Event('blomfree-saved-changed'));
    return next.includes(id);
}
export function allSavedUrl(): string {
    const params = new URLSearchParams();
    for (const type of ['lands','animals','products','vehicles'] as const) {
        const ids = getSavedIds(type);
        if (ids.length) params.set(type, ids.join(','));
    }
    const qs = params.toString();
    return '/saved' + (qs ? '?' + qs : '');
}
