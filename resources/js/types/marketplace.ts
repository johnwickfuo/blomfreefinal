export type MarketplaceDivision = 'gadgets' | 'collections' | 'vehicles' | 'lands' | 'animals';
export interface MarketplaceCardData {
    key: string; id: number; type: MarketplaceDivision;
    title: string; subtitle: string | null; image: string | null;
    price: number; original_price: number | null; discount_percent: number | null;
    stock: number | null; action: string; can_add: boolean; href: string;
    ends_at?: string | null;
}
export interface MarketplaceBanner {
    id: number; eyebrow: string | null; headline: string; description: string | null;
    image: string | null; button_label: string | null; button_url: string | null;
}
export interface MarketplaceSectionData { key: string; title: string; enabled: boolean; order: number }
export const divisionLinks: { key: MarketplaceDivision; label: string; href: string; icon: string; shortLabel: string }[] = [
    { key: 'gadgets', label: 'Gadgets & Electronics', shortLabel: 'Gadgets', href: '/gadgets', icon: 'smartphone' },
    { key: 'collections', label: 'Fashion & Accessories', shortLabel: 'Fashion', href: '/collections', icon: 'shirt' },
    { key: 'vehicles', label: 'BLOMWHEELS Automobile', shortLabel: 'Cars', href: '/automobiles', icon: 'car-front' },
    { key: 'lands', label: 'Estates & Properties', shortLabel: 'Land', href: '/lands', icon: 'map-pin' },
    { key: 'animals', label: 'Kennel & Farm', shortLabel: 'Animals', href: '/kennel-farm', icon: 'paw-print' },
];
