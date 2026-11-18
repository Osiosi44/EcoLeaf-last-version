# Shipping asset provenance

The content photographs below were generated for this demonstration website with the built-in image_gen tool on 2026-11-19. They illustrate garment care and are not presented as real EcoLeaf staff, premises, products, or customer garments. The privacy page states this distinction. Exact prompts, source fingerprints, delivery fingerprints, and processing details are in `docs/image-prompts.json`.

| Asset | Role | Source and processing |
| --- | --- | --- |
| `images/leaf.png` | Header/footer branding and favicon | Original EcoLeaf repository asset, preserved. Branding intentionally repeats. |
| `assets/photos/dry-cleaning.jpg` | Dry cleaning | Generated pressed shirts and blazer on wooden hangers. Resized to 640px width, JPEG quality 84. |
| `assets/photos/wash-fold.jpg` | Wash and fold | Generated folded cotton shirts and towels. Resized to 640px width, JPEG quality 84. |
| `assets/photos/alterations.jpg` | Alterations and repairs | Generated sewing-machine needle, fabric seam, and tailoring tools. Resized to 640px width, JPEG quality 84. |
| `assets/photos/delicate-care.jpg` | Special-care washing | Generated silk blouse and fine-knit sweater with visible textile detail. Resized to 640px width, JPEG quality 84. |
| `assets/photos/ironing.jpg` | Ironing and pressing | Generated steam iron on a cotton shirt. Resized to 640px width, JPEG quality 84. |
| `assets/photos/home-laundry.jpg` | Home story | Generated laundry basket beside a reading chair, illustrating time away from chores. Resized to 1100px width, JPEG quality 84. |
| `assets/photos/about-garment-care.jpg` | About | Generated hands inspecting a linen shirt collar, illustrating attention to garments. Resized to 1100px width, JPEG quality 84. |
| `assets/photos/washer-poster.jpg` | Hero fallback | Screenshot of the actual procedural washer scene, paused with its controls hidden and a flat background for responsive clipping. Captured using Playwright; JPEG quality 88. |
| `assets/washer.js` | Interactive hero | Original procedural geometry authored for EcoLeaf. No downloaded model or external textures. |
| `assets/vendor/three.module.min.js` | 3D renderer | `three@0.169.0` release build; MIT license in `three-LICENSE.txt`. |
| `assets/fonts/Manrope-Variable.ttf` | Typography | Google Fonts `ofl/manrope/Manrope[wght].ttf`; SIL Open Font License in `OFL.txt`. |
| Inline interface icons | Controls and compact home service rows | Original SVG path geometry in `components/layout.php`. |

Original generated PNGs remain in the tool's generated-images directory and are copied under ignored `artifacts/source-images/`. Existing repository photos are preserved locally but excluded from the active hosting package. The previous `laundry-care.jpg` and `images/laundry.jpg` are no longer used by the current pages.

The same canonical service can be identified consistently by its own subject image, but unrelated sections must not reuse that image. Custom services without an explicitly matched image use an icon rather than a misleading stock photograph.
