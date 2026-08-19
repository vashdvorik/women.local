---
version: 1.0
name: Fortun Two public design system
---

# Fortun Two

Fortun Two is a calm, accessible public platform for women entrepreneurs. It uses a white canvas, dark ink for structure and **lavender `#A78FC7` as the only primary accent**. The visual language is compact, warm and editorial rather than promotional.

## Mandatory rules

- Work only inside the `fortuntwo` public theme when changing this theme.
- `#A78FC7` is the main accent for active navigation, focus, links, decorative emphasis and selected controls.
- Do not use `#FFD02F`, `#FFC6C6` or yellow/gold tones as a primary brand or CTA colour.
- Primary actions remain dark (`#1C1C1E`) with white text. Lavender supports the action; it does not replace contrast-critical primary buttons.
- Teal is a restrained supporting surface and may mark success, confirmation or availability; it is never a primary CTA or the dominant page colour.
- Cream is reserved for important warnings or attention states, not ordinary cards.
- Prefer white cards, subtle borders and compact spacing. Avoid oversized empty areas and decorative elements that compete with content.

## Tokens

```yaml
colors:
  primary: "#1C1C1E"
  primary-hover: "#342A42"
  primary-pressed: "#261153"
  accent: "#A78FC7"
  accent-deep: "#53288A"
  accent-soft: "#E9E1F2"
  accent-surface: "#F5F0FA"
  rose: "#E9E1F2"
  teal: "#E2F2F3"
  teal-strong: "#006A77"
  cream-attention: "#FFF3D9"
  coral-soft: "#F6E3DD"
  canvas: "#FFFFFF"
  surface: "#F8FAFB"
  surface-soft: "#FBFBFC"
  hairline: "#E7E7E9"
  hairline-soft: "#F0EDF3"
  hairline-strong: "#CFC6D9"
  ink-deep: "#261153"
  ink: "#1C1C1E"
  charcoal: "#342A42"
  slate: "#585364"
  steel: "#706A79"
  muted: "#AAA4B1"
  success: "#00B473"
  footer: "#1C1C1E"
```

## Typography

Use `Roobert PRO`, then `Noto Sans`, system sans-serif.

| Role | Size | Weight | Line height |
|---|---:|---:|---:|
| Hero | 64–80px desktop / 40–48px mobile | 500 | 1.05 |
| Page heading | 48–60px / 34–42px | 500 | 1.10 |
| Section heading | 36–48px / 28–34px | 500 | 1.15 |
| Card heading | 18–28px | 500 | 1.25 |
| Body | 16px | 400 | 1.5 |
| Supporting text | 14px | 400 | 1.5 |
| Label | 11–12px | 600 | 1.4 |

Headlines use `ink-deep`; body uses `slate` or `charcoal`. Do not use the accent colour for long text.

## Layout and spacing

- Main container: `min(1360px, calc(100% - 64px))`; mobile: `calc(100% - 32px)`.
- Use 8px rhythm: 8, 12, 16, 20, 24, 32, 40, 48, 64px.
- Normal sections: 64px vertical padding. Large feature sections: 96px only when content needs it.
- Cards: 16–28px radius, white background, 1px soft border. Use shadows sparingly.
- Keep all public pages aligned to the same container and header/footer structure.

## Components

- Primary button: dark fill, white text, 44px minimum height, full pill radius.
- Secondary button: transparent, `hairline-strong` border, dark text.
- Accent button/tag: `accent-soft` fill, `accent-deep` text, only for secondary emphasis.
- Active menu item and focus ring: `accent` / `accent-deep`.
- Status success: teal/green only; warning: `cream-attention` only.
- Feature-card palette: white, rose, teal, accent-surface and coral-soft. Balance variants; do not make every card lavender.
- Footer: dark background, white text, muted links.

## Accessibility

- Maintain readable contrast; never put white text directly on `#A78FC7` unless contrast is verified.
- Show `:focus-visible` with a 2px accent outline.
- Preserve semantic headings, alt text and keyboard operation of menus.
