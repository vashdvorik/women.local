version: 1.0
name: women-editorial-luxury-design-system
description: "A restrained, high-contrast, editorial-luxury design system for a premium women-focused digital product. The visual language combines deep chocolate surfaces, porcelain light areas, warm caramel and nude accents, monumental editorial typography, cinematic photography, asymmetric composition, generous whitespace and quiet, precise UI. The system is content-agnostic: it defines style, tokens, component behavior and visual rules only. It must not prescribe page sections, messaging, copy or information architecture."

colors:
  primary: "#2A1913"
  on-primary: "#FFF9F5"

  brand-chocolate-950: "#160E0B"
  brand-chocolate-900: "#21140F"
  brand-chocolate-850: "#2A1913"
  brand-chocolate-800: "#34231F"
  brand-chocolate-700: "#493129"

  brand-cognac-700: "#74472F"
  brand-cognac-600: "#8E5B3D"
  brand-caramel-500: "#B9855B"
  brand-caramel-400: "#C99A73"
  brand-caramel-300: "#DBB89B"

  brand-nude-300: "#D7B9A6"
  brand-nude-200: "#E6CFC1"
  brand-nude-100: "#F1E3DA"

  brand-porcelain-100: "#FAEEE8"
  brand-porcelain-50: "#FFF9F5"
  brand-ivory: "#FFFDFC"

  canvas: "#FFFDFC"
  surface: "#FAF4F1"
  surface-soft: "#FCF8F6"
  surface-warm: "#FAEEE8"
  surface-nude: "#F1E3DA"
  surface-featured: "#E8D1C2"
  surface-dark: "#21140F"
  surface-dark-deep: "#160E0B"

  ink-deep: "#21140F"
  ink: "#34231F"
  charcoal: "#49342B"
  slate: "#756158"
  steel: "#8C776E"
  stone: "#9C887F"
  muted: "#B8A8A0"

  on-dark: "#FFF9F5"
  on-dark-muted: "#DCC8BC"

  hairline: "#E9DCD5"
  hairline-soft: "#F2E8E3"
  hairline-strong: "#D8C5BA"
  hairline-dark: "#594037"

  success-accent: "#526B5A"
  warning-accent: "#9B6A3A"
  error-accent: "#8C4842"
  error-soft: "#F4E6E2"

typography:
  display-family:
    fontFamily: "Prata"
    fallback: "Georgia, Times New Roman, serif"

  ui-family:
    fontFamily: "Manrope"
    fallback: "Inter, Arial, sans-serif"

  hero-display:
    fontFamily: "Prata"
    fontSize: 96px
    fontWeight: 400
    lineHeight: 0.98
    letterSpacing: -2.6px

  display-lg:
    fontFamily: "Prata"
    fontSize: 72px
    fontWeight: 400
    lineHeight: 1.02
    letterSpacing: -2px

  heading-1:
    fontFamily: "Prata"
    fontSize: 56px
    fontWeight: 400
    lineHeight: 1.08
    letterSpacing: -1.2px

  heading-2:
    fontFamily: "Prata"
    fontSize: 44px
    fontWeight: 400
    lineHeight: 1.12
    letterSpacing: -0.8px

  heading-3:
    fontFamily: "Prata"
    fontSize: 32px
    fontWeight: 400
    lineHeight: 1.18
    letterSpacing: -0.3px

  heading-4:
    fontFamily: "Manrope"
    fontSize: 22px
    fontWeight: 600
    lineHeight: 1.30

  heading-5:
    fontFamily: "Manrope"
    fontSize: 18px
    fontWeight: 600
    lineHeight: 1.35

  subtitle:
    fontFamily: "Manrope"
    fontSize: 20px
    fontWeight: 400
    lineHeight: 1.55

  body-lg:
    fontFamily: "Manrope"
    fontSize: 18px
    fontWeight: 400
    lineHeight: 1.65

  body-md:
    fontFamily: "Manrope"
    fontSize: 16px
    fontWeight: 400
    lineHeight: 1.60

  body-md-medium:
    fontFamily: "Manrope"
    fontSize: 16px
    fontWeight: 500
    lineHeight: 1.55

  body-sm:
    fontFamily: "Manrope"
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.55

  body-sm-medium:
    fontFamily: "Manrope"
    fontSize: 14px
    fontWeight: 500
    lineHeight: 1.50

  caption:
    fontFamily: "Manrope"
    fontSize: 13px
    fontWeight: 400
    lineHeight: 1.45

  caption-bold:
    fontFamily: "Manrope"
    fontSize: 13px
    fontWeight: 600
    lineHeight: 1.45

  micro:
    fontFamily: "Manrope"
    fontSize: 12px
    fontWeight: 500
    lineHeight: 1.40

  micro-uppercase:
    fontFamily: "Manrope"
    fontSize: 11px
    fontWeight: 600
    lineHeight: 1.40
    letterSpacing: 1.2px
    textTransform: uppercase

  button-md:
    fontFamily: "Manrope"
    fontSize: 14px
    fontWeight: 600
    lineHeight: 1.30

  stat-display:
    fontFamily: "Prata"
    fontSize: 76px
    fontWeight: 400
    lineHeight: 1.00
    letterSpacing: -2px

rounded:
  xs: 4px
  sm: 6px
  md: 8px
  lg: 12px
  xl: 16px
  xxl: 20px
  xxxl: 24px
  feature: 28px
  image: 18px
  full: 9999px

spacing:
  xxs: 4px
  xs: 8px
  sm: 12px
  md: 16px
  lg: 20px
  xl: 24px
  xxl: 32px
  xxxl: 40px
  block-sm: 48px
  block: 64px
  block-lg: 80px
  section-sm: 96px
  section: 120px
  section-lg: 144px
  section-xl: 180px

layout:
  maxWidth: 1440px
  contentWidth: 1280px
  wideContentWidth: 1360px
  desktopColumns: 12
  tabletColumns: 8
  mobileColumns: 4
  desktopGutter: 24px
  tabletGutter: 20px
  mobileGutter: 16px
  desktopMargin: 64px
  wideMargin: 80px
  tabletMargin: 32px
  mobileMargin: 20px

motion:
  fast: 140ms
  default: 220ms
  slow: 420ms
  cinematic: 700ms
  easing: "cubic-bezier(.22,.61,.36,1)"

Overview

This design system defines a premium editorial visual language for a women-focused digital product. It is intentionally content-agnostic. It does not define page sections, page order, marketing copy, messaging, business logic, user flows, or information architecture.

The visual goal is not “feminine decoration.” The goal is refined confidence: a combination of editorial fashion language, private-club restraint, premium business aesthetics and modern product clarity.

The interface should feel expensive because of composition, typography, proportion, photography and whitespace — not because of gold, gradients, excessive ornament or decorative effects.

The system should remain consistent across all generated pages, regardless of content. Any new screen should feel like part of the same visual world even when its content structure is completely different.

Key Characteristics

Deep chocolate surfaces as the primary structural anchor.

Porcelain and ivory surfaces instead of sterile white.

Caramel used sparingly as a jewelry-like accent.

Nude used for warmth, not as the dominant background.

Monumental serif typography for expressive display text.

Neutral grotesk typography for UI, labels and long-form reading.

Strong contrast between very large and very small type.

Asymmetric editorial composition rather than repetitive SaaS grids.

Generous whitespace used as an active design element.

Cinematic, real, human photography with warm-neutral grading.

Low visual noise and low component density.

Few card styles and minimal reliance on boxed content.

Restrained borders and almost invisible shadows.

No “pink = women” cliché.

No decorative luxury clichés such as metallic gold, marble, glitter or glossy gradients.

Colors

Palette Source

The core palette is built around four families:

Chocolate — structure, depth, authority.

Caramel / Cognac — action, emphasis, connection.

Nude — warmth and supporting surfaces.

Porcelain / Ivory — breathing room and editorial clarity.

The palette must remain warm, tactile and neutral. Saturated cool colors should not be introduced except for necessary semantic states.

Brand & Accent

Brand Chocolate 950 ({colors.brand-chocolate-950})

Deepest background. Use for the darkest visual moments, full-width dark surfaces, overlays and footer-level depth.

Brand Chocolate 900 ({colors.brand-chocolate-900})

Primary premium dark surface. Use for major dark containers, dark navigation, feature panels and strong visual anchors.

Brand Chocolate 850 ({colors.brand-chocolate-850})

Primary dark UI tone and deep accent.

Brand Chocolate 800 ({colors.brand-chocolate-800})

Primary dark text and supporting surface tone.

Brand Chocolate 700 ({colors.brand-chocolate-700})

Softened dark brown for secondary emphasis.

Cognac 700 ({colors.brand-cognac-700})

High-contrast warm action color for links, focus states and darker accent moments.

Cognac 600 ({colors.brand-cognac-600})

Primary warm interactive accent.

Caramel 500 ({colors.brand-caramel-500})

Signature accent. Use for primary CTA, key dividers, section indices, tiny graphic details and restrained highlights.

Caramel 400 ({colors.brand-caramel-400})

Softer supporting accent.

Caramel 300 ({colors.brand-caramel-300})

Light decorative accent and subtle warm border.

Nude 300 ({colors.brand-nude-300})

Supporting warm surface.

Nude 200 ({colors.brand-nude-200})

Soft warm surface.

Nude 100 ({colors.brand-nude-100})

Large quiet tinted surface.

Porcelain 100 ({colors.brand-porcelain-100})

Warm light surface.

Porcelain 50 ({colors.brand-porcelain-50})

Primary warm light background.

Ivory ({colors.brand-ivory})

Lightest canvas. Use instead of pure #FFFFFF.

Color Proportion

Recommended overall color balance:

Chocolate family: 30–45%.

Porcelain / Ivory family: 40–55%.

Nude family: 8–15%.

Caramel / Cognac family: 3–7%.

Caramel must never become a large continuous background unless a specific composition requires a deliberate, temporary accent interruption.

Surface

Canvas ({colors.canvas})

Default page canvas. Warm and almost white.

Surface ({colors.surface})

Quiet section surface, useful when a subtle tonal change is needed without creating a new visual zone.

Surface Soft ({colors.surface-soft})

Very light separation layer.

Surface Warm ({colors.surface-warm})

Porcelain callout surface.

Surface Nude ({colors.surface-nude})

Warm editorial surface.

Surface Featured ({colors.surface-featured})

Stronger warm surface for one featured object or localized emphasis.

Surface Dark ({colors.surface-dark})

Primary dark container.

Surface Dark Deep ({colors.surface-dark-deep})

Deepest visual background.

Text

Ink Deep ({colors.ink-deep})

Primary text on light surfaces.

Ink ({colors.ink})

Default headline and strong body text.

Charcoal ({colors.charcoal})

Secondary dark emphasis.

Slate ({colors.slate})

Secondary copy and metadata.

Steel ({colors.steel})

Tertiary text.

Stone ({colors.stone})

Caption and non-essential metadata.

Muted ({colors.muted})

Disabled and placeholder text only.

On Dark ({colors.on-dark})

Primary text on chocolate backgrounds.

On Dark Muted ({colors.on-dark-muted})

Secondary text on dark backgrounds.

Semantic Colors

Semantic colors must remain muted and harmonious with the warm palette.

Success Accent ({colors.success-accent})

Muted forest green.

Warning Accent ({colors.warning-accent})

Muted warm amber-brown.

Error Accent ({colors.error-accent})

Muted warm red.

Do not use bright green, orange or red unless required by platform-level safety or compliance.

Contrast Rules

Never place caramel body text on porcelain if it reduces readability.

Never use nude for critical small text.

Long-form body copy on light backgrounds should use Ink Deep, Ink or Charcoal.

Essential text on dark backgrounds should use On Dark.

Decorative text may use On Dark Muted only if contrast remains accessible.

Typography

Typography Philosophy

Typography is the strongest visual signature of the system.

The system uses a deliberate two-family contrast:

Prata — editorial display voice.

Manrope — rational product voice.

The serif communicates sophistication, personality and scale.

The sans-serif communicates clarity, precision and usability.

Do not use the serif for buttons, form labels, long body text, dense tables or utility navigation.

Do not use heavy display weights to create hierarchy. Hierarchy should come from size, whitespace, contrast and placement.

Font Family

Prata

Primary display serif.

Use for:

hero-scale text;

large headings;

editorial statements;

large numbers when emotional rather than purely functional;

quotes;

high-impact short phrases.

Recommended weight: 400 only.

Manrope

Primary UI sans-serif.

Use for:

navigation;

body text;

buttons;

form controls;

captions;

metadata;

tags;

tables;

filters;

micro labels.

Allowed weights: 400, 500, 600.

Avoid 700–800 unless an accessibility or system constraint requires it.

Fallback Strategy

Display fallback:

Georgia, Times New Roman, serif.

UI fallback:

Inter, Arial, sans-serif.

Hierarchy

{typography.hero-display}

96px / 400 / 0.98 / -2.6px

Use for the single largest typographic object in a viewport.

{typography.display-lg}

72px / 400 / 1.02 / -2px

Use for major editorial statements.

{typography.heading-1}

56px / 400 / 1.08 / -1.2px

Use for page-level expressive headings.

{typography.heading-2}

44px / 400 / 1.12 / -0.8px

Use for strong section-level headings when content requires them.

{typography.heading-3}

32px / 400 / 1.18 / -0.3px

Use for smaller editorial headings.

{typography.heading-4}

22px / 600 / 1.30

Use for structured card or panel titles.

{typography.heading-5}

18px / 600 / 1.35

Use for compact titles.

{typography.subtitle}

20px / 400 / 1.55

Use for high-emphasis supporting text.

{typography.body-lg}

18px / 400 / 1.65

Use for spacious explanatory copy.

{typography.body-md}

16px / 400 / 1.60

Default body.

{typography.body-md-medium}

16px / 500 / 1.55

Default medium emphasis.

{typography.body-sm}

14px / 400 / 1.55

Secondary body.

{typography.body-sm-medium}

14px / 500 / 1.50

Controls and compact metadata.

{typography.caption}

13px / 400 / 1.45

Captions and helper text.

{typography.caption-bold}

13px / 600 / 1.45

Small emphasis.

{typography.micro}

12px / 500 / 1.40

Microcopy.

{typography.micro-uppercase}

11px / 600 / 1.40 / 1.2px tracking

Section markers, utility labels and system taxonomy.

{typography.button-md}

14px / 600 / 1.30

Buttons.

{typography.stat-display}

76px / 400 / 1.00 / -2px

Large editorial metrics.

Typography Principles

Use extreme scale contrast.

A large serif element should often be paired with very small sans-serif metadata.

Avoid visual monotony created by multiple 28–36px elements of similar importance.

Do not center all headings.

Use left alignment by default.

Center alignment is reserved for intentionally ceremonial or singular compositions.

Display text should usually be short enough to breathe.

Long paragraphs should never use the display serif.

Body copy should generally remain 55–75 characters per line.

Recommended maximum text widths:

hero/display: 720px.

lead: 640px.

body: 560px.

narrow editorial note: 440px.

Do not tighten display line-height below 0.96.

Do not use uppercase for long phrases.

Uppercase is reserved for micro labels, indices and compact navigation.

Layout

Grid & Container

Wide viewport maximum width: 1440px.

Primary content width: 1280px.

Optional wide content width: 1360px.

Desktop: 12-column grid.

Tablet: 8-column grid.

Mobile: 4-column grid.

Desktop gutters: 24px.

Tablet gutters: 20px.

Mobile gutters: 16px.

Desktop outer margin: 64px.

Wide desktop outer margin: 80px.

Tablet outer margin: 32px.

Mobile outer margin: 20px.

Asymmetry

The grid should support controlled asymmetry.

Do not default to equal-width columns.

Preferred editorial relationships include:

5 / 7.

4 / 8.

7 / 5.

3 / 6 / 3.

8 / 4.

2 / 7 / 3.

Whitespace may intentionally occupy unused grid columns.

Negative space is a valid design object.

Alignment

Use strong shared baselines.

Align unrelated components less often than a typical dashboard would.

Avoid creating a rigid “everything fits into boxes” feeling.

Use occasional edge-to-edge visual objects while preserving readable text margins.

Spacing System

Base unit: 4px.

Primary rhythm: 8px.

Tokens:

{spacing.xxs} 4px.

{spacing.xs} 8px.

{spacing.sm} 12px.

{spacing.md} 16px.

{spacing.lg} 20px.

{spacing.xl} 24px.

{spacing.xxl} 32px.

{spacing.xxxl} 40px.

{spacing.block-sm} 48px.

{spacing.block} 64px.

{spacing.block-lg} 80px.

{spacing.section-sm} 96px.

{spacing.section} 120px.

{spacing.section-lg} 144px.

{spacing.section-xl} 180px.

Whitespace Philosophy

Whitespace is a primary luxury signal.

Do not fill empty areas with decoration.

Do not use additional cards to “balance” an empty composition.

Do not add text merely because an area looks empty.

Large surfaces should have breathing room around the dominant object.

When in doubt, remove 15–25% of the visual elements before adding new ones.

Visual Density

The system supports three density levels:

Low density

Use for emotionally important or high-impact visual moments.

Medium density

Default for structured content.

High density

Reserved for utility-heavy views such as directories, tables, search, filters or account interfaces.

Do not keep every viewport at the same density.

Elevation & Depth

The interface is predominantly flat.

Depth comes from:

tonal contrast;

photography;

layer overlap;

subtle borders;

small z-axis separation.

Shadow Level 0

No shadow.

Border: {colors.hairline-soft}.

Use for most cards and containers.

Shadow Level 1

rgba(33, 20, 15, 0.04) 0px 4px 16px 0px

Use for subtle interactive lift.

Shadow Level 2

rgba(33, 20, 15, 0.06) 0px 12px 32px 0px

Use for selected floating cards.

Shadow Level 3

rgba(22, 14, 11, 0.12) 0px 24px 64px 0px

Use very sparingly for overlays or intentional floating objects.

Do not use strong black drop shadows.

Do not add glow.

Do not use neumorphism.

Decorative Depth

Dark surfaces may use extremely subtle tonal light:

radial gradient from rgba(185,133,91,0.10) to transparent.

The gradient must not read as a visible “effect.”

Large solid areas may optionally use a 1.5–2.5% monochrome noise layer to prevent sterile digital flatness.

Texture must remain almost imperceptible.

Shapes

Border Radius Scale

{rounded.xs}

4px.

Tiny controls.

{rounded.sm}

6px.

Small badges and utility controls.

{rounded.md}

8px.

Inputs and compact surfaces.

{rounded.lg}

12px.

Standard UI cards.

{rounded.xl}

16px.

Larger cards and image containers.

{rounded.xxl}

20px.

Selected editorial surfaces.

{rounded.xxxl}

24px.

Featured surface.

{rounded.feature}

28px.

Rare large highlighted container.

{rounded.image}

18px.

Default editorial image radius.

{rounded.full}

9999px.

Pills, chips and button variants that explicitly require a pill shape.

Radius Philosophy

Do not over-round every object.

The visual system should feel refined, not bubbly.

Default content cards should usually use 8–16px.

Large feature blocks may use 20–28px.

Editorial separators may use no radius at all.

Use pill radius only when the element semantically benefits from a capsule shape.

Photography Geometry

Default editorial portrait: 4:5.

Default landscape visual: 16:10 or 3:2.

Feature portrait: 3:4 or 4:5.

Avoid arbitrary blob masks.

Avoid circular portraits except for compact avatar UI.

Avoid floral frames and ornamental clipping.

Use confident rectangular crops with restrained corner radii.

Photography

Photography Direction

Photography is a core part of the design system.

It must feel like an editorial business or fashion portrait, not generic stock photography.

Subjects should appear:

confident;

intelligent;

present;

self-directed;

active;

natural.

Avoid stereotypical “women in business” poses.

Avoid groups staring at a laptop and smiling unnaturally.

Avoid overly staged handshake images.

Avoid overly glossy beauty photography.

Lighting

Preferred:

natural window light;

soft directional studio light;

warm late-afternoon light;

side lighting;

deep soft shadows;

controlled highlights.

Avoid:

flat office fluorescent lighting;

overexposure;

heavy HDR;

strong orange cast;

bright commercial stock lighting.

Color Grading

Shadows: neutral chocolate-brown.

Highlights: cream / porcelain.

Skin tones: natural and warm-neutral.

Environment: slightly desaturated.

Black point: soft, never crushed unless intentionally cinematic.

Contrast: medium-high.

Saturation: moderate to low.

Composition

Prefer:

off-center subjects;

negative space;

foreground depth;

partial cropping;

architectural framing;

3/4 portrait angles;

authentic gestures;

people listening, presenting, writing, walking, speaking or working.

Use details strategically:

hands;

paper;

notebook;

microphone;

fabric;

jewelry;

laptop;

architecture;

coffee only when contextually natural.

Do not use lifestyle clichés as decoration.

Image Treatment

Do not place heavy color overlays on portraits.

If text overlaps photography, use local tonal darkening or a subtle gradient only where needed for readability.

Keep faces natural.

Do not introduce synthetic glow.

Do not over-retouch.

Iconography

Icon Style

1.5px stroke.

Geometric but not sterile.

Minimal internal detail.

Soft optical corners.

Consistent optical size.

Use line icons by default.

Filled icons are reserved for semantic status or compact utility states.

Icon Color

On light: Chocolate 800 or Cognac 600.

On dark: Porcelain 50 or Caramel 400.

Avoid multicolor icon sets.

Avoid gradients inside icons.

Icon Density

Do not place icons in every card by default.

Maximum recommended visible decorative icons in one medium-density viewport: 4.

If the title explains the function sufficiently, omit the icon.

Graphic Signature

The Thread

The system may use one recurring 1px caramel line as a signature graphic device.

The Thread may appear as:

a short rule;

an underline;

a connector;

a timeline;

a partial curve;

a subtle border transition;

a geometric fragment.

The Thread should imply connection and continuity.

It must not become decorative clutter.

Do not use more than one prominent Thread composition in the same viewport.

Brand Motif

If the brand identity includes a lotus, flower, crown or similar symbol, use the full mark primarily in the logo.

For decorative usage, extract a simplified geometric fragment.

Decorative motif rules:

scale: 300–700px.

opacity on light: 4–8%.

opacity on dark: 5–10%.

stroke only.

no fill.

no metallic effect.

partial crop is preferred.

Components

General Component Philosophy

Components should feel quiet, precise and secondary to content.

The UI should never look like a component showcase.

Avoid excessive borders.

Avoid excessive cards.

Avoid equal visual weight across all controls.

Use 2–3 card families maximum on the same screen.

Buttons

button-primary

Primary action.

Background: {colors.brand-caramel-500}.

Text: {colors.brand-chocolate-950}.

Typography: {typography.button-md}.

Height: 52px.

Padding: 0 28px.

Radius: {rounded.full}.

Optional icon: simple arrow, 16px.

Pressed state: {colors.brand-cognac-600}.

Disabled background: {colors.hairline}.

Disabled text: {colors.muted}.

button-primary-dark

Primary action on light editorial surfaces when a darker action is needed.

Background: {colors.brand-chocolate-900}.

Text: {colors.on-dark}.

Height: 52px.

Padding: 0 28px.

Radius: {rounded.full}.

button-secondary-light

For dark surfaces.

Background: transparent.

Text: {colors.on-dark}.

Border: 1px solid rgba(255,255,255,0.30).

Height: 52px.

Padding: 0 26px.

Radius: {rounded.full}.

button-secondary-dark

For light surfaces.

Background: transparent.

Text: {colors.ink-deep}.

Border: 1px solid {colors.hairline-strong}.

Height: 52px.

Padding: 0 26px.

Radius: {rounded.full}.

button-ghost

Background: transparent.

Text: {colors.ink}.

Padding: 8px 12px.

Radius: {rounded.md}.

button-link

Background: transparent.

Text: {colors.brand-cognac-600}.

Typography: {typography.body-sm-medium}.

Padding: 0.

Optional arrow may translate 3–4px on interaction.

Button Rules

Use one dominant primary action within a local visual zone.

Do not place three equally prominent buttons side by side.

Do not use gradients.

Do not use heavy shadows.

Do not use glossy or metallic styling.

Do not use oversized icon buttons unless required by the product interaction.

Cards & Containers

card-editorial

Default non-boxed content grouping.

Background: transparent.

Border top: 1px solid {colors.hairline}.

Padding top: 24px.

Radius: 0.

Use when visual grouping can be achieved by spacing and a rule rather than a box.

card-base

Background: {colors.canvas}.

Border: 1px solid {colors.hairline-soft}.

Radius: {rounded.lg}.

Padding: 24px.

Shadow: none.

card-soft

Background: {colors.surface-soft}.

Border: 1px solid {colors.hairline-soft}.

Radius: {rounded.xl}.

Padding: 28px.

card-nude

Background: {colors.surface-nude}.

Text: {colors.ink-deep}.

Radius: {rounded.xxl}.

Padding: 32px.

card-featured

Background: {colors.surface-featured}.

Text: {colors.ink-deep}.

Radius: {rounded.xxxl}.

Padding: 40px.

card-dark

Background: {colors.surface-dark}.

Text: {colors.on-dark}.

Radius: {rounded.xxxl}.

Padding: 40px.

Border: 1px solid rgba(255,255,255,0.06).

card-photo

Background: transparent.

Padding: 0.

Image radius: {rounded.image}.

Text block spacing: 20–24px after image.

Card Rules

Prefer fewer, larger content objects over many equal cards.

Do not build repetitive rows of 6–8 identical cards unless the information architecture genuinely requires it.

Use whitespace and typography before introducing a card border.

Cards should not all use the same radius.

Avoid icon-in-circle + title + paragraph as the default card pattern.

Inputs & Forms

text-input

Height: 52px.

Background: transparent or {colors.canvas} depending on surface.

Text: {colors.ink-deep}.

Border: 1px solid {colors.hairline-strong}.

Radius: {rounded.md}.

Padding: 0 16px.

Placeholder: {colors.stone}.

text-input-dark

Height: 52px.

Background: rgba(255,255,255,0.03).

Text: {colors.on-dark}.

Border: 1px solid rgba(255,255,255,0.20).

Radius: {rounded.md}.

Placeholder: {colors.on-dark-muted}.

Focus State

Border: 1px solid {colors.brand-cognac-600}.

Box shadow: 0 0 0 3px rgba(185,133,91,0.12).

Do not use generic bright-blue focus rings unless required by platform accessibility standards.

select-input

Follow text-input geometry.

Use minimal chevron.

No filled gray native-looking box if a custom UI layer is available.

checkbox

18–20px.

Radius: 4px.

Selected background: {colors.brand-chocolate-900}.

Check: {colors.on-dark}.

radio

18–20px.

Selected inner dot: {colors.brand-caramel-500}.

Outer border: {colors.hairline-strong}.

Tabs

tab-text

Default tabs should prefer simple text with an underline rather than heavy filled pills.

Inactive text: {colors.slate}.

Active text: {colors.ink-deep}.

Active underline: 1px solid {colors.brand-caramel-500}.

tab-pill

Use only where a compact categorical control genuinely benefits from a capsule shape.

Inactive:

background: transparent.

border: 1px solid {colors.hairline-strong}.

text: {colors.slate}.

Active:

background: {colors.brand-chocolate-900}.

text: {colors.on-dark}.

Badges & Tags

badge-outline

Background: transparent.

Border: 1px solid {colors.hairline-strong}.

Text: {colors.charcoal}.

Typography: {typography.micro}.

Padding: 6px 10px.

Radius: {rounded.full}.

badge-dark

Background: {colors.brand-chocolate-900}.

Text: {colors.on-dark}.

Padding: 6px 10px.

Radius: {rounded.full}.

badge-caramel

Use sparingly.

Background: {colors.brand-caramel-300}.

Text: {colors.brand-chocolate-900}.

Padding: 6px 10px.

Radius: {rounded.full}.

badge-success

Background: rgba(82,107,90,0.12).

Text: {colors.success-accent}.

Border: 1px solid rgba(82,107,90,0.24).

Tag Rules

Tags should remain visually quiet.

Do not use bright multi-color chip systems unless the product logic requires semantic category colors.

Do not place more than 3–4 tags under one content object by default.

Tables & Dense Data

table-container

Background: {colors.canvas}.

Border: 1px solid {colors.hairline}.

Radius: {rounded.lg}.

table-header

Background: {colors.surface-soft}.

Text: {colors.slate}.

Typography: {typography.micro-uppercase}.

table-row

Background: {colors.canvas}.

Border bottom: 1px solid {colors.hairline-soft}.

Typography: {typography.body-sm}.

Selected row:

background: {colors.surface-warm}.

Dense data should remain precise and product-like. Do not force decorative editorial typography into tables.

Navigation

Navigation Philosophy

Navigation is quiet, architectural and restrained.

It should not compete with the primary visual content.

Desktop nav height: 72–76px.

Sticky nav height: 64px.

Light navigation:

Background: rgba(255,253,252,0.92).

Text: {colors.ink-deep}.

Border bottom: 1px solid {colors.hairline-soft}.

Backdrop blur: 16px when sticky.

Dark navigation:

Background: rgba(22,14,11,0.92).

Text: {colors.on-dark}.

Border bottom: 1px solid rgba(255,255,255,0.08).

Backdrop blur: 16px.

Nav Link

Typography: Manrope 13px / 500.

No oversized buttons for every nav item.

Active state:

text color remains strong;

optional 1px caramel underline.

Dropdown

Background: {colors.canvas}.

Text: {colors.ink-deep}.

Border: 1px solid {colors.hairline}.

Radius: {rounded.lg}.

Shadow: Level 2.

Padding: 12px.

Utility Components

divider-light

1px solid {colors.hairline}.

divider-dark

1px solid rgba(255,255,255,0.10).

section-index

Typography: {typography.micro-uppercase}.

Color on light: {colors.brand-cognac-600}.

Color on dark: {colors.brand-caramel-400}.

Use for small taxonomy and navigational orientation.

quote-mark

If used, it must be subtle.

Color: {colors.brand-caramel-500}.

Do not use oversized decorative quotation-mark icons as a default.

avatar

Compact UI avatar: circular.

Editorial profile portrait: rectangular 4:5 with {rounded.image}.

Do not mix avatar geometry with editorial portrait geometry.

progress

Track: {colors.hairline-soft}.

Fill: {colors.brand-chocolate-900} or {colors.brand-caramel-500} depending on emphasis.

No gradients.

scrollbar

If custom styling is used:

Track: transparent.

Thumb: rgba(52,35,31,0.24).

Hover: rgba(52,35,31,0.38).

Motion & Interaction

Motion Philosophy

Motion should feel expensive, calm and deliberate.

It should never feel playful, springy or gamified unless the product itself explicitly requires that behavior.

Global Timing

Fast: {motion.fast}.

Default: {motion.default}.

Slow: {motion.slow}.

Cinematic: {motion.cinematic}.

Easing: {motion.easing}.

Entrance Motion

Preferred:

opacity 0 → 1.

translateY 16–24px → 0.

Duration: 420–700ms.

Optional for large imagery:

subtle clip reveal.

Avoid:

bounce;

overshoot;

spin;

large horizontal fly-in;

random animation directions.

Hover & Pressed

Buttons:

subtle background shift;

arrow translation 3–4px;

no lift greater than 2px.

Cards:

optional image scale to 1.015–1.025.

Optional border darkening.

Avoid dramatic floating.

Links:

underline may grow from left to right.

Images:

avoid aggressive zoom.

Motion Accessibility

Respect prefers-reduced-motion.

When reduced motion is enabled:

remove translate;

remove clip reveals;

use opacity-only or no animation.

Responsive Behavior

Breakpoints

Mobile Small

< 480px.

Mobile

480–767px.

Tablet

768–1023px.

Desktop

1024–1439px.

Wide Desktop

≥ 1440px.

Responsive Typography

Hero Display:

Wide: 96px.

Desktop: 80px.

Tablet: 64px.

Mobile: 48px.

Mobile Small: 40px.

Display Large:

Wide: 72px.

Desktop: 64px.

Tablet: 52px.

Mobile: 40px.

Heading 1:

Wide: 56px.

Desktop: 52px.

Tablet: 44px.

Mobile: 36px.

Heading 2:

Wide: 44px.

Desktop: 42px.

Tablet: 36px.

Mobile: 30px.

Stat Display:

Desktop: 76px.

Tablet: 64px.

Mobile: 52px.

Responsive Layout

Desktop:

12 columns.

Tablet:

8 columns.

Mobile:

4 columns.

Asymmetric desktop layouts should recompose rather than simply shrink.

Do not preserve awkward empty columns on mobile.

Large side-by-side layouts should stack intentionally.

Mobile should keep hierarchy, not density.

Responsive Spacing

Wide desktop section spacing:

120–180px depending on density.

Desktop:

96–144px.

Tablet:

80–112px.

Mobile:

64–88px.

Mobile Small:

56–72px.

Touch Targets

Minimum interactive target: 44×44px.

Primary buttons: 52px height.

Inputs: 52px height.

Icon buttons: 44×44px minimum on touch devices.

Responsive Radius

Keep radius values mostly consistent across breakpoints.

Do not inflate mobile rounding.

Large featured containers may reduce from 28px to 20–24px on small mobile if space is limited.

Image Behavior

Photography should preserve intentional crop and subject focus.

Use object-fit: cover.

Use object-position based on subject placement, not center by default.

Portrait images should preserve 4:5 or 3:4 where possible.

Landscape editorial visuals should preserve 16:10 or 3:2.

Do not stretch images.

Do not use low-resolution thumbnails as large feature imagery.

Dark overlays should be local and readability-driven.

Decorative System

Decorative elements are optional and subordinate.

Allowed:

The Thread;

single geometric line motifs;

oversized low-opacity brand fragment;

very subtle paper/noise texture;

restrained warm tonal gradient on dark surfaces.

Not allowed:

sparkles;

glitter;

glossy gold;

marble;

flowers used as generic “feminine” decoration;

rose-pink gradients;

random blobs;

neon accents;

glassmorphism;

heavy grain;

3D metallic objects unless the brand system explicitly calls for them.

Do's and Don'ts

Do

Use deep chocolate as the structural anchor.

Use porcelain and ivory instead of pure white.

Use caramel as a rare, high-value accent.

Use nude for warmth and subtle surface variation.

Use serif display typography for expressive scale.

Use sans-serif for all functional UI.

Use asymmetry.

Use empty grid columns intentionally.

Use large, confident photography.

Use real, human, editorial moments.

Use one dominant visual object per viewport.

Use fewer, larger components.

Use hairline borders.

Use almost invisible shadows.

Use large spacing between unrelated content groups.

Use strong typographic hierarchy.

Use large + tiny type contrast.

Use calm motion.

Use low-saturation warm photography.

Use rectangular photography with restrained radii.

Use the Thread sparingly as a signature.

Don't

Do not make the interface pink by default.

Do not use purple gradients.

Do not use gold as the main luxury signal.

Do not use glitter, marble or metallic textures.

Do not use generic stock “business women smiling at laptop” imagery.

Do not use excessive floral decoration.

Do not use icons inside circles everywhere.

Do not turn every content group into a card.

Do not use six identical cards when fewer larger objects can communicate the same structure.

Do not over-round every container.

Do not use heavy shadows.

Do not use glassmorphism.

Do not use neon colors.

Do not introduce cool saturated accents without semantic necessity.

Do not center every heading.

Do not use serif type in buttons or form controls.

Do not use bold display serif.

Do not use decorative gradients on primary buttons.

Do not use multiple equally strong CTA colors.

Do not make every screen equally dense.

Do not use pure black unless required by a specific product surface.

Do not use pure white as the default page canvas.

Do not add decorative elements merely to fill empty space.

AI Visual Consistency Rules

When generating any interface using this system, the model must preserve the following hierarchy of priorities:

1. Composition before decoration.

2. Typography before iconography.

3. Whitespace before additional containers.

4. Photography before illustration when human presence is needed.

5. Chocolate / porcelain contrast before introducing new colors.

6. Caramel only for intentional emphasis.

7. Asymmetry before repetitive equal grids.

8. Fewer larger objects before many small cards.

9. Flat surfaces before shadows.

10. Editorial restraint before “feminine” ornament.

AI must not infer that a women-focused product requires pink, flowers, soft gradients or playful rounded shapes.

AI must interpret “premium” as:

precision;

space;

contrast;

scale;

materials implied through color and photography;

typographic confidence;

restraint.

AI must interpret “feminine” as:

warmth;

elegance;

humanity;

refinement;

not as decoration.

AI must preserve the warm palette throughout the experience.

AI must not introduce unrelated visual languages between pages.

AI must not invent new corner-radius styles without a functional reason.

AI must not use more than one dominant accent color in the same component.

AI must not make all surfaces dark. Dark and light areas must alternate according to content needs while maintaining the same visual language.

AI must not make all surfaces light. Deep chocolate is essential to brand recognition.

AI must keep long-form usability high even in editorial compositions.

AI must keep utility-heavy surfaces more neutral and less expressive than marketing or storytelling surfaces while preserving the same tokens.

Quality Control Checklist

Before accepting any generated screen, verify:

Does the screen still look premium without decorative graphics?

Is there one dominant visual hierarchy?

Is the warm chocolate / porcelain palette clearly recognizable?

Is caramel used sparingly?

Are there too many equal cards?

Can 15–20% of visual elements be removed without loss of meaning?

Is there enough negative space?

Are display serif and UI sans-serif used correctly?

Are body copy widths controlled?

Are shadows almost invisible?

Are corners restrained rather than bubbly?

Does photography feel editorial rather than stock?

Are dark and light surfaces visually balanced?

Does the composition feel designed rather than template-generated?

Does the interface avoid stereotypical “women’s website” clichés?

Does the visual language remain consistent with previous screens?

If several answers are “no”, simplify and regenerate.

Signature Visual Formula

Deep chocolate structure.

Porcelain breathing room.

Caramel jewelry-like accent.

Warm nude support.

Monumental serif.

Precise grotesk UI.

Asymmetric grid.

Large cinematic photography.

Hairline borders.

Minimal shadows.

Controlled radius.

Generous whitespace.

Low visual noise.

One dominant visual object.

Quiet motion.

Editorial confidence.

Iteration Guide

When adjusting the design, modify one visual dimension at a time.

Examples:

If the page feels cheap, first reduce component density before adding visual effects.

If the page feels flat, increase contrast between dark and light surfaces before adding shadows.

If the page feels generic, strengthen typography scale and asymmetry before adding decoration.

If the page feels too masculine, soften photography, surface warmth and spacing — do not add pink.

If the page feels too soft, deepen chocolate surfaces and increase scale contrast — do not add black or heavy bold type.

If the page feels too corporate, increase editorial whitespace, portrait scale and serif presence.

If the page feels too editorial and not usable enough, strengthen Manrope UI hierarchy and container logic without changing the brand palette.

When introducing a new component:

Reuse existing color tokens.

Reuse existing type tokens.

Choose the smallest appropriate radius.

Default to no shadow.

Default to transparent or porcelain background.

Use caramel only when the element deserves high emphasis.

Do not add a new visual language for one isolated component.

Known Gaps

Exact brand logo geometry is not defined in this file.

Exact production font licensing and hosting strategy are not defined.

Dark-mode is not treated as a separate theme because deep chocolate surfaces are already part of the primary brand language.

Motion timing may be adjusted for platform performance, but the calm easing character must remain.

Semantic data visualization colors are not fully defined; any future chart palette should remain muted, warm and accessible.

Highly specialized product modules may require additional tokens, but all extensions must preserve the same visual hierarchy, typography, color logic, spacing rhythm and restraint.
