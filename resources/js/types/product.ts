export type SelectOption = {
    id: number;
    name: string;
};

export type MediaOption = SelectOption & {
    mime: string;
    url: string;
};

export type GalleryItem = {
    media_id: number;
    alt_text: string;
    caption: string;
};

export type SpecificationItem = {
    label: string;
    value: string;
    unit: string;
};

export type SpecificationGroup = {
    name: string;
    items: SpecificationItem[];
};

export type ProductFormData = {
    expected_lock_version: number | null;
    requested_slug: string;
    name: string;
    summary: string;
    description: string;
    category_id: number | null;
    brand_id: number | null;
    type_id: number | null;
    is_featured: boolean;
    seo_title: string;
    seo_description: string;
    og_media_id: number | null;
    gallery: GalleryItem[];
    brochure_media_id: number | null;
    highlights: string[];
    spec_groups: SpecificationGroup[];
    related_product_ids: number[];
};

export type ProductOptions = {
    categories: SelectOption[];
    brands: SelectOption[];
    types: SelectOption[];
    media: MediaOption[];
    products: SelectOption[];
};