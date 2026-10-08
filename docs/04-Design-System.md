# Design System

**Versi dokumen:** 4.0 · **Diperbarui:** 5 Oktober 2026  
**Status:** spesifikasi implementasi; belum ada bukti aplikasi berjalan dalam paket ini.  
**Kontrak runtime:** Laravel 13.x + PHP **8.3.x**. PHP maksimum cPanel 8.3 dikonfirmasi pengguna; kemampuan hosting lain mengikuti [matriks verifikasi](00-Decisions-and-Hosting-Matrix.md).  
**Kontrak domain:** [Data Model & Business Rules](06-Data-Model-and-Business-Rules.md). **Pengujian:** [Acceptance](08-Acceptance-and-Traceability.md).
## PT Cahya Intan Medika
### Shared Visual Language for Blade Public Site and React CMS

**Design Style:** Clean Flat Design + purposeful Bento Grid  
**Public UI:** Blade + Tailwind CSS  
**CMS UI:** React + TypeScript + Inertia + Tailwind CSS + shadcn/ui  
**Design Contract:** Shared CSS/Tailwind tokens across both surfaces  
**Accessibility Target:** WCAG 2.2 AA baseline

---

# 1. Design System Goals

Design system harus:

- Menjaga konsistensi visual antara public website dan CMS.
- Memungkinkan Blade dan React menggunakan token yang sama.
- Mengurangi hard-coded style di component.
- Mempercepat pembuatan screen baru.
- Menjaga accessibility dan responsive behavior.
- Memisahkan brand language dari implementation library.

Komponen public dan CMS tidak wajib memiliki implementation yang sama, tetapi harus menggunakan visual contract yang sama.

---

# 2. Brand Palette

## Primary Palette

| Token | Hex | Usage |
|---|---|---|
| `brand-navy-900` | `#03045e` | Major heading, dark surface, high-emphasis CTA, footer |
| `brand-blue-700` | `#0077b6` | Primary brand action, active state, links |
| `brand-cyan-500` | `#00b4d8` | Decorative accent/progress; focus hanya pada surface dengan kontras teruji |
| `brand-cyan-200` | `#90e0ef` | Soft interactive surface, hover, highlight |
| `brand-cyan-100` | `#caf0f8` | Light background, badges, empty-state surface |

## Neutral Extensions

| Token | Value | Usage |
|---|---|---|
| `white` | `#FFFFFF` | Base surface |
| `surface-page` | `#F7FCFE` | Page background |
| `text-muted` | `#4F6F8F` | Secondary text |
| `border` | `#D8EDF4` | Decorative card border; bukan satu-satunya batas control |
| `border-soft` | `#EAF7FA` | Subtle divider/border |

Neutral colors support readability and are not a competing brand palette.

---

# 3. Semantic Tokens

Never use raw brand color everywhere. Map colors to semantic roles.

Recommended variables:

```css
:root {
  --color-brand-dark: #03045e;
  --color-brand-primary: #0077b6;
  --color-brand-accent: #00b4d8;
  --color-brand-soft: #90e0ef;
  --color-brand-surface: #caf0f8;

  --color-background: #f7fcfe;
  --color-surface: #ffffff;
  --color-text: #03045e;
  --color-text-muted: #4f6f8f;
  --color-border: #d8edf4;
  --color-border-soft: #eaf7fa;
  --color-control-border: #4f6f8f;
  --color-focus-on-light: #0077b6;
  --color-focus-on-dark: #caf0f8;
  --color-text-on-tint: #03045e;
}
```

Semantic colors also include:

- success.
- warning.
- destructive/error.
- info.

These should use accessible semantic values and do not need to be forced into the blue brand palette.

---

# 4. Color Usage Rules

## Navy `#03045e`

Use for:

- Major headings.
- Footer.
- Dark feature cards.
- High-emphasis CTA.
- High-priority admin states where appropriate.

Avoid:

- Entire page backgrounds by default.
- Long paragraph text on dark surfaces without contrast verification.

## Blue `#0077b6`

Use for:

- Primary brand buttons.
- Links.
- Active navigation/filter.
- Primary interactive icon accents.

## Cyan `#00b4d8`

Use for:

- Scroll progress.
- Secondary accent.
- Focus accents hanya pada background yang lulus pemeriksaan kontras.
- Small interactive indicators.

Avoid long paragraph text in cyan.

## Cyan Soft `#90e0ef`

Use for:

- Hover surface.
- Information highlight.
- Secondary emphasis.

## Cyan Surface `#caf0f8`

Use for:

- Soft cards.
- Badges.
- Empty states.
- Section tint.
- Active sidebar background.

---

# 5. Typography

Primary typeface:

```text
Inter
```

Load as a bundled/local webfont or approved webfont asset through the project build pipeline. Do not depend on `next/font`.

## Font Families

```css
--font-sans: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
```

## Weight Scale

Recommended:

- 400 Regular.
- 500 Medium.
- 600 Semibold.
- 700 Bold.

Avoid excessive font weights unless a real visual need exists.

## Public Type Scale

Desktop reference:

| Role | Size |
|---|---:|
| Hero H1 | 72–88px |
| Page H1 | 56–80px |
| H2 | 44–60px |
| H3 | 24–32px |
| H4 | 18–22px |
| Body Large | 17–18px |
| Body | 14–16px |
| Small/Meta | 11–13px |

Mobile reference:

```text
H1 42–50px
H2 34–42px
H3 22–28px
Body 14–16px
```

Large headings may use subtle negative tracking.

Eyebrow/meta labels may use positive tracking and uppercase carefully.

## CMS Type Scale

CMS should be denser:

```text
Page title      28–36px
Section title   20–24px
Card title      16–20px
Body            14–16px
Table/meta      12–14px
```

---

# 6. Spacing System

Use a consistent spacing scale.

Recommended baseline:

```text
4
8
12
16
20
24
32
40
48
64
80
96
```

Use Tailwind spacing tokens where possible.

Rules:

- Prefer tokenized spacing over arbitrary values.
- Page section spacing should be more generous on public pages.
- CMS prioritizes density and predictable alignment.

---

# 7. Layout Tokens

## Public Container

Recommended:

```text
max-width: 1380px
large desktop horizontal padding: 24px
mobile horizontal padding: 16px
```

## CMS Container

Use fluid content width with sensible max-width per page type.

Forms/editor pages may be narrower than dashboards/tables.

## Grid

Public Bento desktop:

```text
12 columns
12–16px gap
```

Common spans:

```text
3
4
5
6
7
8
12
```

Tablet:

```text
1–2 columns depending content
```

Mobile:

```text
1 column
```

---

# 8. Radius Tokens

Recommended:

```text
radius-sm       10px
radius-control  12px
radius-card     18px
radius-panel    24px
radius-hero     28px
radius-pill     999px
```

Avoid mixing many arbitrary radii.

---

# 9. Shadow Tokens

Default card:

```css
box-shadow: 0 8px 22px rgba(3, 4, 94, 0.04);
```

Major surface:

```css
box-shadow: 0 14px 36px rgba(3, 4, 94, 0.08);
```

Focus on border/surface hierarchy before relying on shadows.

Avoid dramatic 3D/neomorphic shadows.

---

# 10. Border System

Use:

- `border-soft` for dividers/subtle containers.
- `border` for decorative cards; `control-border` for input/select/checkbox boundaries needed to identify controls.
- brand border for active/focus emphasis.

Avoid thick decorative borders unless part of a specific feature component.

---

# 11. Button System

## Variants

### Primary Dark

```text
background: #03045e
text: white
```

Use for highest-emphasis action on suitable light surfaces.

### Primary Brand

```text
background: #0077b6
text: white
```

Default brand action.

### Accent

```text
background: #00b4d8
text: verified high-contrast foreground
```

Use sparingly.

### Secondary

```text
white/light surface
blue-tinted border
brand text
```

### Ghost

Transparent surface with visible hover/focus.

### Destructive

Semantic red/destructive token.

## Sizes

Recommended:

- `sm`.
- `md` default.
- `lg`.
- icon-only.

Minimum touch target should remain accessible on mobile.

## States

- default.
- hover.
- focus-visible.
- active.
- disabled.
- loading.

Loading button should retain width to avoid layout shift.

---

# 12. Link System

Links must remain visually distinguishable from plain text.

Patterns:

- Brand blue text.
- Underline on hover/focus or persistent underline where context requires.
- Clear focus-visible outline.

Never remove focus outline without replacement.

---

# 13. Input System

Applies to:

- input.
- select.
- textarea.
- combobox.
- date controls.
- search.

Requirements:

- Visible label.
- Help text when useful.
- Error text association.
- Consistent height/radius.
- Visible focus ring.

Focus reference:

```text
border: var(--color-control-border)
focus ring on light: #0077b6, solid
focus ring on navy: #caf0f8, solid
width: 2px; offset: 2px
```

Error remains semantic red.

---

# 14. Checkbox / Radio / Switch

Rules:

- Entire label row should be clickable when practical.
- Keyboard accessible.
- State should not depend on color alone.
- Destructive settings should not use ambiguous switch labels.

---

# 15. Badge System

Badge categories:

- Neutral.
- Brand.
- Info.
- Success.
- Warning.
- Destructive.

Common CMS status badge examples:

```text
DRAFT
PUBLISHED
ARCHIVED
OPEN
CLOSED
NEW
IN_PROGRESS
RESOLVED
SPAM
```

Status badges require text labels; color is supplemental.

---

# 16. Card System

## Public Card Variants

### Standard

```text
white
thin blue-tinted border
subtle shadow
```

### Dark Feature

```text
background #03045e
white text
```

### Soft Cyan

```text
background #caf0f8
```

### Image

- contextual image.
- overlay only where text readability needs it.

### Metric

- large value.
- short label.
- optional supporting note.

## CMS Cards

Favor:

- compact padding.
- clear title/action alignment.
- data hierarchy.
- less decorative imagery.

---

# 17. Bento Grid Rules

Bento must express hierarchy, not decoration.

Use different spans only when content importance or function warrants it.

Avoid:

- random asymmetry.
- excessive card variants.
- making every section a Bento grid.

Good use:

- Home capability overview.
- Dashboard KPI overview.
- Activity editorial hierarchy.

---

# 18. Navigation Tokens

Public navbar:

- white/light surface.
- subtle border/shadow on scroll.
- active item brand/soft surface.

CMS sidebar active state:

```text
background #caf0f8
text #03045e
```

CMS sidebar hover:

- soft background.
- no excessive animation.

---

# 19. Breadcrumb

Use on:

- Product Detail.
- Activity Detail.
- Career Detail.
- CMS nested editors/details.

Rules:

- Current page not required to be a link.
- Use semantic navigation landmark.
- Avoid overly long breadcrumb chains.

---

# 20. Table System

CMS table anatomy:

- header.
- sortable columns where relevant.
- row data.
- status.
- row actions.
- pagination.

Recommended states:

- loading skeleton rows.
- empty state.
- error/retry.

Use sticky headers only when large dataset UX benefits.

Do not use sticky first columns without testing mobile/keyboard behavior.

---

# 21. Pagination

Use server-side pagination for large CMS/public datasets.

Controls:

- previous.
- next.
- current page.
- total/summary where useful.

Maintain filters/search across page navigation.

---

# 22. Dialog / Modal

Use for:

- confirmation.
- compact secondary forms.
- focused short tasks.

Avoid using modal for complex full-screen editors.

Requirements:

- focus trap.
- escape handling where safe.
- focus return to trigger.
- accessible title/description.

---

# 23. Drawer / Sheet

Use for:

- mobile navigation.
- compact filter panels.
- secondary contextual controls.

Do not use nested drawers where avoidable.

---

# 24. Toast System

Use for transient operation result.

Variants:

- success.
- info.
- warning.
- error.

Toasts must not be the only presentation of critical validation issues.

---

# 25. Alert / Inline Message

Use for persistent contextual state:

- warning.
- permission limitation.
- unsaved changes.
- destructive consequences.
- operational outage.

Alerts should include icon/text but remain understandable without icon/color.

---

# 26. Skeleton System

Skeleton should roughly match final content geometry.

Use on:

- dashboard cards.
- tables.
- cards/list.
- detail header.

Avoid large shimmering surfaces that create distraction.

---

# 27. Empty State Component

An empty state may include:

- icon/illustration.
- title.
- explanation.
- primary next action.

CTA must honor permission.

---

# 28. File Upload Component

Required features depending context:

- Dropzone.
- Browse button.
- accepted format.
- size limit.
- preview.
- file error.
- upload progress where needed.
- replace/remove.
- alt text/caption for media.

Private CV uploader must clearly indicate:

```text
Private document — accessible only to authorized admin users.
```

---

# 29. Image Treatment

Use `object-fit` rules intentionally.

Product imagery:

- preserve product visibility.
- avoid aggressive crop.

Editorial imagery:

- crop allowed if composition remains meaningful.

All meaningful images require appropriate alt text.

Decorative images use empty alt where semantically appropriate.

---

# 30. Iconography

Use one consistent icon family per interface surface where practical.

Rules:

- Icon-only buttons require accessible label/tooltip if meaning is not obvious.
- Use icons as support, not replacement for critical text.
- Keep stroke weight visually consistent.

---

# 31. Motion Tokens

Recommended:

```text
motion-fast      150–200ms
motion-standard  250–350ms
motion-reveal    450–650ms
```

Easing should feel responsive, not bouncy by default.

## Public Motion

Allowed:

- reveal.
- scroll progress.
- subtle parallax.
- sticky/pin transitions.
- horizontal rail feedback.

## CMS Motion

Limit to:

- control feedback.
- dialog/sheet transitions.
- toast.
- expand/collapse.

No storytelling motion.

---

# 32. Z-Index Scale

Define a small consistent scale.

Example:

```text
base          0
sticky       10
dropdown     20
header       30
overlay      40
modal        50
toast        60
```

Do not use arbitrary `z-index: 999999`.

---

# 33. Breakpoint Strategy

Use Tailwind's breakpoint system as base, but design should respond to content rather than device names.

Test specifically around:

- mobile narrow.
- mobile wide.
- tablet.
- laptop.
- desktop.
- wide desktop.

---

# 34. Focus System

All interactive components require `:focus-visible` treatment.

Recommended appearance:

- 2px ring or equivalent.
- `focus-on-light` blue pada white/light surface; `focus-on-dark` pale cyan pada navy.
- Jangan menurunkan opacity sehingga rasio kontras tidak memenuhi target.
- sufficient offset from component border.

Focus must remain visible on dark and light surfaces.

---

# 35. Accessibility Tokens / Rules

Required:

- Minimum readable contrast.
- Focus visible.
- Touch targets reasonable.
- Heading hierarchy.
- Semantic HTML.
- ARIA only when native semantics are insufficient.
- Reduced motion.
- Live-region only when meaningful.

Do not solve accessibility only with component library defaults; test composed screens.

---

# 36. Tailwind Integration

Centralize shared values in Tailwind/CSS configuration.

Preferred:

- semantic CSS variables.
- Tailwind utilities referring to variables.
- reusable component variants.

Avoid repeating raw hex values throughout templates/components.

Blade and React must import/use the same theme contract.

---

# 37. Blade Component Strategy

Recommended public primitives:

```text
resources/views/components/
├─ ui/
│  ├─ button.blade.php
│  ├─ badge.blade.php
│  ├─ card.blade.php
│  ├─ container.blade.php
│  ├─ heading.blade.php
│  ├─ input.blade.php
│  └─ breadcrumb.blade.php
└─ public/
   ├─ navbar.blade.php
   ├─ footer.blade.php
   ├─ product-card.blade.php
   └─ ...
```

Blade components should stay presentation-focused.

Complex business logic remains in controllers/services/view models as appropriate.

---

# 38. React Component Strategy

Recommended CMS layers:

```text
resources/js/components/ui/
resources/js/components/admin/
resources/js/features/
resources/js/pages/
```

`components/ui` contains reusable primitives.

`features` contains domain-oriented reusable behavior.

`pages` compose routes/screens and should not become a dumping ground for shared logic.

---

# 39. shadcn/ui Usage Rules

Use shadcn/ui as source-controlled React UI primitives for CMS.

Guidelines:

- Adapt to project tokens.
- Remove unused variants.
- Keep accessibility behavior.
- Do not import component merely because it exists.
- Avoid deep wrapper layers around simple primitives.

Public Blade surface does not depend on shadcn/ui.

---

# 40. Design System Governance

When adding a new visual pattern:

1. Check if existing token/component can support it.
2. Extend existing primitive if variation is genuinely reusable.
3. Create new pattern only when semantics differ.
4. Document new token/variant if it becomes shared.
5. Validate mobile, keyboard, contrast, and reduced motion.

Avoid premature universal components.

---

# 41. Do / Don't

## Do

- Use shared semantic tokens.
- Keep visual hierarchy strong.
- Use real content examples during QA.
- Design mobile intentionally.
- Keep public JS lightweight.
- Keep CMS dense but readable.
- Use semantic status colors.

## Don't

- Hard-code raw palette values in many components.
- Use Bento everywhere.
- Hide core information behind animation.
- Use color as the only status indicator.
- Introduce multiple icon systems casually.
- Mix many border radii/shadows.
- Bring public motion patterns into CMS.

---

# 42. Design System Acceptance Criteria

- [ ] Shared color tokens exist.
- [ ] Blade and React use the same token contract.
- [ ] Typography scale documented.
- [ ] Spacing/radius/shadow tokens documented.
- [ ] Button/input/card/badge patterns implemented.
- [ ] Focus-visible treatment works.
- [ ] Semantic states exist.
- [ ] Public motion respects reduced motion.
- [ ] CMS component variants match brand system.
- [ ] Raw colors/arbitrary spacing are minimized.
- [ ] Critical components pass keyboard/accessibility QA.

# 43. Kontrak kontras v4

| Foreground / indicator | Background | Rasio perkiraan | Penggunaan |
|---|---|---:|---|
| #0077b6 | #ffffff | 4,87:1 | Brand text normal dan focus ring pada putih |
| #03045e | #00b4d8 | 7,20:1 | Teks navy pada tombol accent cyan |
| #00b4d8 | #ffffff | 2,46:1 | Dekorasi; tidak menjadi satu-satunya focus indicator |
| #d8edf4 | #ffffff | 1,21:1 | Divider/card dekoratif; tidak menjadi satu-satunya batas input |
| #4f6f8f | #caf0f8 | 4,33:1 | Jangan gunakan untuk teks normal; gunakan text-on-tint navy |

Rasio dihitung dari token opaque, bukan screenshot aplikasi. WCAG AA text normal >=4,5:1; applicable non-text indicator >=3:1. Screen yang tersusun tetap harus diuji pada seluruh state/background termasuk disabled exceptions yang relevan. Target sentuh internal 44x44 CSS px bila memungkinkan; table action dapat memakai hit area tanpa memperbesar ikon.

Semantic success/warning/destructive/info harus memiliki pasangan foreground/background yang diuji sebelum dipakai; tidak mengklaim aksesibilitas otomatis dari library. Source aturan ada di dokumen 00 bagian 5.

Typography memakai rem/clamp yang tetap reflow pada 320 CSS px dan zoom; ukuran px di tabel merupakan referensi visual, bukan alasan untuk memaksa heading overflow. Critical body copy memakai baseline 16px public dan 14px CMS, dengan user zoom tetap bekerja.
