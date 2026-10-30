export interface VehicleCardData {
    id: number; title: string; slug: string; make: string; model: string; year: number;
    condition: string; body_type: string | null; mileage_km: number | null;
    transmission: string | null; fuel_type: string | null;
    state: string | null; city: string | null; price: number; negotiable: boolean;
    status: string; image: string | null; href: string;
}
export interface VehicleDetails extends VehicleCardData {
    stock_reference: string; trim: string | null; engine: string | null;
    drivetrain: string | null; seats: number | null; exterior_color: string | null;
    interior_color: string | null; description: string; features: string[];
    known_issues: string[]; accident_history: string | null;
    service_history: string | null; customs_status: string | null;
    documents: string[]; gallery: string[]; video_url: string | null;
}
export interface VehiclePagination {
    data: VehicleCardData[]; current_page: number; last_page: number;
    total: number; links: { url: string | null; label: string; active: boolean }[];
}
export function formatVehiclePrice(value: number): string {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(value);
}
export function conditionLabel(value: string): string {
    return { brand_new: 'Brand New', foreign_used: 'Foreign Used', nigerian_used: 'Nigerian Used' }[value] ?? value;
}
