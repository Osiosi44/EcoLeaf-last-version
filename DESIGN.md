---
name: EcoLeaf
description: Green laundry care with clear booking and precise account tools.
colors:
  ink: "#183c30"
  text: "#344c43"
  muted: "#5d7167"
  green: "#156c45"
  green-dark: "#0e5034"
  mint: "#e9f3eb"
  canvas: "#f7faf7"
  line: "#dce6df"
  white: "#fff"
  error: "#9e2929"
  focus: "#257653"
  field-border: "#aebfb4"
  pending-bg: "#f7edcd"
  pending-text: "#73541b"
  progress-bg: "#e1edf9"
  progress-text: "#285a83"
  complete-bg: "#dfefdf"
  complete-text: "#286037"
  cancelled-bg: "#f5e6e4"
  cancelled-text: "#8d3936"
typography:
  display:
    fontFamily: "Manrope, Arial, sans-serif"
    fontSize: "clamp(3rem, 6.6vw, 6rem)"
    fontWeight: 700
    lineHeight: 1.06
    letterSpacing: "-0.04em"
  headline:
    fontFamily: "Manrope, Arial, sans-serif"
    fontSize: "clamp(2rem, 3.4vw, 3.3rem)"
    fontWeight: 700
    lineHeight: 1.16
    letterSpacing: "-0.035em"
  title:
    fontFamily: "Manrope, Arial, sans-serif"
    fontSize: "1.2rem"
    fontWeight: 700
    lineHeight: 1.35
    letterSpacing: "-0.02em"
  body:
    fontFamily: "Manrope, Arial, sans-serif"
    fontSize: "15px"
    lineHeight: 1.65
  label:
    fontFamily: "Manrope, Arial, sans-serif"
    fontSize: "13px"
    fontWeight: 700
rounded:
  field: "8px"
  control: "9px"
  notice: "10px"
  panel-small: "12px"
  panel: "16px"
  photo: "20px"
  circle: "50%"
spacing:
  field-gap: "8px"
  action-gap: "14px"
  form-gap: "20px"
  panel-padding: "30px"
  section-mobile: "60px"
  section-tablet: "70px"
  section-desktop: "100px"
components:
  button-primary:
    backgroundColor: "{colors.green}"
    textColor: "{colors.white}"
    rounded: "{rounded.control}"
    padding: "13px 23px"
  button-primary-hover:
    backgroundColor: "{colors.green-dark}"
    textColor: "{colors.white}"
  button-outline:
    backgroundColor: "transparent"
    textColor: "{colors.green}"
    rounded: "{rounded.control}"
    padding: "13px 23px"
  input:
    backgroundColor: "{colors.white}"
    textColor: "{colors.ink}"
    rounded: "{rounded.field}"
    padding: "11px 13px"
  form-panel:
    backgroundColor: "{colors.canvas}"
    rounded: "{rounded.panel}"
    padding: "30px"
  status-pending:
    backgroundColor: "{colors.pending-bg}"
    textColor: "{colors.pending-text}"
    rounded: "20px"
    padding: "5px 11px"
---

# Design System: EcoLeaf

## Overview

EcoLeaf keeps its established green leaf identity in a modern, polished web interface. White space, forest text, mint surfaces, and a single Manrope family give public pages an open, calm character. Booking, account, and administration screens use the same palette with denser, clearly labeled controls.

Rounded controls and circular washer geometry connect the interface to laundry care. The desktop home composition and the narrow service editor are representative implementations, captured in `artifacts/qa/home-viewport.png` and `artifacts/qa/admin-services-390.png`.

**Key Characteristics:**

- Green branding with restrained tonal surfaces.
- Large balanced headings and readable, compact form labels.
- Line icons, clear focus rings, and explicit action states.
- Responsive grids with an optional procedural washer illustration.

This is an extraction of the current web implementation, not a catalog world. Sources: `styles.css`, `components/layout.php`, `assets/washer.js`, and `docs/asset-provenance.md`. Product commitments live in `PRODUCT.md`; page direction lives in `docs/redesign-brief.md`. The sidecar records extensions and source pointers. Readable Impeccable guidance was applied; its engine, detector, and comp-gates were unavailable. Local browser checks passed and the independent finish review returned SHIP; these are not claims that CLI design gates ran or that the public host was updated.

## Colors

### Primary

Forest green is the action color for buttons, links, current navigation, icons, and reached order steps. Deep green is its hover treatment. Mint supports care strips, selected admin navigation, icons, and quiet contextual surfaces.

### Secondary

Pale blue is reserved for in-progress status and information notices. Pending uses warm yellow, completed uses soft green, and cancelled uses muted red. Status wording accompanies color.

### Neutral

White is the main page background. Ink carries headings and field text; text carries paragraphs; muted carries supporting descriptions. Canvas distinguishes form panels and footer regions, while line creates low-contrast separators and card borders. Field borders are stronger than general separators. Error and focus have distinct functional colors.

**The Green Identity Rule.** Preserve the existing leaf mark and green action hierarchy across public, account, and admin screens.

## Typography

Manrope is self-hosted from `assets/fonts/Manrope-Variable.ttf`, declared for weights 200–800 with `font-display: swap`; Arial and sans-serif are fallbacks. There is no second display or mono family.

The frontmatter records the base heading hierarchy. Public display headings are balanced with tight tracking. Compact pages use smaller heading clamps; administration uses a 34px section heading, becoming 29px on phones. Body copy is limited to 70ch; the home introduction uses a narrower 440px measure with more leading. Body size becomes 14px at 600px and below. Buttons use 14px, weight 700; supporting copy is generally 11–14px. Prices and dashboard figures use tabular numerals.

## Layout

The desktop container is `min(1200px, calc(100% - 80px))`. Gutters become 28px at 1100px, 20px at 800px, and 18px at 600px. These are observed values rather than a universal spacing scale; the frontmatter names the repeated role-based steps.

The sticky header is 84px tall, reducing to 74px at 1100px and 72px at 600px. Public navigation becomes a toggle-controlled vertical menu at 1100px. Its expanded content can scroll within the available viewport.

Desktop home uses a two-column 1.05:1 hero, broad section spacing, and linear service rows. At 600px the hero stacks, images follow copy, service prices move beneath descriptions, and steps/reviews become one column. Sections use 100px vertical padding, 70px at 800px, and 60px at 600px. At 360px the hero actions stack.

Booking uses a flexible selection column and a 350px sticky summary, narrowing the summary to 310px at 1100px. At 800px it becomes one column and the summary is static. Form grids generally collapse at 800px; pickup fields collapse at 600px. Account and authentication compositions stack at 600px.

Admin uses a 200px sidebar and flexible content, narrowing the sidebar to 170px at 1100px. At 800px its navigation becomes a horizontally scrollable row above content. Tables retain a scrollable local wrapper; on phones they have a 650px minimum width. Catalog option prices retain two columns on phones and become one at 360px. The footer becomes two navigation columns beneath the brand at 600px.

## Elevation & Depth

Most surfaces are separated by canvas/mint fills and thin borders. The ambient shadow token is `0 12px 35px rgba(24,60,48,.09)`, used by the skip link. Expanded mobile navigation has `0 14px 24px rgba(24,60,48,.08)`. Primary button hover uses `0 6px 14px rgba(21,108,69,.13)` and a 2px upward movement. Panels and service rows remain visually quiet. The procedural washer supplies the strongest material depth through soft shadows and metallic/glass surfaces.

## Shapes

Fields have gently curved corners; controls use a slightly rounder profile. Form panels and order cards use the panel radius; catalog forms and tables use the smaller panel radius. Photographs and the closing call to action use the photo radius. Circle buttons, avatars, and step markers echo the washer door. The home illustration stage has an arched `47% 47% 25px 25px` silhouette, changing to `46% 46% 20px 20px` on phones. Thin borders keep controls distinguishable against pale surfaces.

## Components

### Buttons

Primary buttons combine white labels with green fill, a 1px transparent border, and a 52px minimum height. Hover deepens the fill, lifts the control, and adds the small shadow; active returns it to baseline with `scale(.98)`. Outline buttons have an `#adc3b5` border and transparent fill, becoming mint on hover. Light buttons are white on green sections. Danger buttons use error red and darken to `#7e2020`. Small buttons and circle icon buttons are 44px; regular buttons become 50px minimum on phones. Disabled buttons use 0.55 opacity and a not-allowed cursor.

All interactive controls share a visible 3px focus outline with 4px offset. Keep icon-only controls labeled for assistive technology.

### Cards / Containers

Form panels use canvas fill and 30px padding; on phones this becomes `24px 20px`. Order cards use white, a line border, and 30px padding, becoming `22px 18px`. The booking summary uses canvas with 28px padding, reducing to 24px. Catalog editors use canvas, the small panel radius, and 25px padding; phone editors use `20px 15px`.

### Inputs / Fields

Fields are white with the field border, 48px minimum height, and `11px 13px` padding. Labels sit 8px above their control. Hover changes the border to green; focus uses the shared outline. Invalid fields use the error border and nearby error text; hints remain muted. Textareas resize vertically. Password fields reserve room for a labeled visibility control. Quantity controls combine 44px decrease/increase buttons with a centered numeric input inside one bordered rounded group.

### Navigation

Public navigation uses 13px semibold labels, 44px minimum link targets, and an underline plus heavier green text for the current page. The mobile menu removes that underline and uses larger labels. Admin links use mint fill and green text for the selected section, with 48px minimum height. Account tabs use a green lower border for the current tab. Keep `aria-current` synchronized with the visible state.

### Status and disclosure

Order badges use compact 11px bold text, 30px minimum height, and rounded ends. Pending, in progress, complete, and cancelled have their own foreground/background pairs. Service prices, FAQs, booking groups, and catalog editors use native disclosure controls with divider lines and chevrons. Notices carry error, success, or information styling and alert/status semantics.

### Icons, imagery, and washer

Interface icons are authored inline SVG from `components/layout.php`: 24-unit view box, current-color stroke, 1.7 stroke width, round ends and joins, generally 20px with contextual size variants. Decorative SVG is hidden from assistive technology. Preserve the existing leaf PNG for branding; do not replace this system with emoji or icon fonts.

Use a distinct subject-specific illustrative image for each canonical laundry service, with separate home-story and About images. The photographs are AI-generated illustrations, not evidence of actual staff or facilities. Exact prompts, fingerprints, and processing are recorded in `docs/image-prompts.json` and `docs/asset-provenance.md`. The washer in `assets/washer.js` remains generic procedural geometry; its own captured poster is the fallback if rendering fails or the WebGL context is lost. Brand marks may repeat; unrelated content imagery must not be recycled between sections. Service catalog thumbnails are 170 x 115px on desktop, 120 x 90px below 800px, and 88px square below 600px. Home service rows retain compact icons; unmatched custom services use an icon rather than an unrelated image.

Motion uses `cubic-bezier(.16,1,.3,1)` for lifted controls and the home arrival. Button background changes take 0.2s and transform/shadow changes 0.25s. Home copy and illustration arrive over 0.8s and 1s from a 12px offset. Reduced-motion preference removes CSS animation/transitions and smooth scrolling, and starts the washer paused. The washer offers labeled play/pause and rotation buttons; desktop pointer drag rotates it, while touch preserves vertical scrolling. Rendering pauses offscreen or when the document is hidden. Reduced motion still permits explicit user-controlled play and rotation.

## Do's and Don'ts

### Do:

- **Do** preserve the existing leaf mark and green action hierarchy.
- **Do** use the shared type, field, focus, and button treatments across customer and admin screens.
- **Do** retain labels and status text alongside icons and colors.
- **Do** retain responsive local table scrolling, mobile navigation, reduced-motion handling, and the washer fallback.
- **Do** record every shipping image's provenance and keep illustrative photography honestly described.

### Don't:

- **Don't** replace the confirmed green identity or the authored SVG system with unrelated accents or emoji.
- **Don't** make the booking task depend on animation or WebGL.
- **Don't** invent business claims, reviews, statistics, facilities, or photographic provenance.
- **Don't** describe unavailable Impeccable CLI gates as executed.
