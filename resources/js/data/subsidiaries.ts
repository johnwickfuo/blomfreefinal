import type { Component } from 'vue';
import { MapPin, PawPrint, Shirt, Smartphone, CarFront } from 'lucide-vue-next';

export interface Subsidiary {
    name: string;
    tagline: string;
    href: string;
    icon: Component;
    image?: string;
    comingSoon?: boolean;
}

export const subsidiaries: Subsidiary[] = [
    {
        name: 'BLOMFREE Real Estate',
        tagline: 'Discover quality land and property opportunities',
        href: '/lands',
        icon: MapPin,
    },
    {
        name: 'BLOMFREE Kennel & Farm',
        tagline: 'Explore carefully raised and sourced animals',
        href: '/kennel-farm',
        icon: PawPrint,
    },
    {
        name: 'BLOMFREE Collections',
        tagline: 'Discover fashion and accessories for everyday confidence',
        href: '/collections',
        icon: Shirt,
    },
    {
        name: 'BLOMFREE Gadgets & Accessories',
        tagline: 'Shop practical technology, electronics and devices',
        href: '/gadgets',
        icon: Smartphone,
    },
    { name: 'BLOMWHEELS Automobile', tagline: 'Explore brand-new, foreign-used and Nigerian-used vehicles', href: '/automobiles', icon: CarFront },
];
