# UI/UX & Design System Guidelines for Antigravity

When designing, building, refactoring, auditing, or styling UI components, dashboards, landing pages, or user flows in this workspace:

## 1. Skill Coordination & Hierarchy
- **Central Coordinator**: Use `senior-ui-ux-orchestrator` as the primary routing layer for UI/UX workflows.
- **Design Intelligence**: Query `ui-ux-pro-max` (via `python3 skill/hub.py search <query>` or `python3 skill/ui-ux-agent-skill-system/core/skills/ui-ux-pro-max/scripts/search.py`) for curated styles (67 styles), palettes (161 colors), font pairings (57 typography rules), and UX rules.
- **Brand Identity & Templates**: Use `open-design` to bind or reference brand systems (150+ brands including Apple, Airbnb, Stripe, BMW, Linear) and layout templates (dashboards, pitch decks, landing pages).
- **Core Product UI**: Use `webapp-ui-skill` for operational dashboards, data tables, metrics, filters, and dense state coverage (loading, empty, error, disabled, focus, submitting, success).
- **Quality & Critique**: Use `design-critic-skill` and `ux-audit-skill` for visual hierarchy, typography contrast, and heuristic audits.

## 2. Design Doctrine & Anti-Patterns
- **No Marketing Fluff in Dashboards**: Do not put large marketing heroes or decorative floating cards inside operational deep learning dashboards. Prioritize data density, readability, chart clarity, and filter stability.
- **Token Consistency**: Use 4px/8px grid intervals. Base radius 6-8px. Preserve 4.5:1 text contrast minimum.
- **State Completeness**: Always implement explicit empty, loading, error, and active states for asynchronous data operations (training loss charts, model inference, metric summaries).
- **Local-First & Evidence**: Never fake testing or audit results. When claiming checks, ensure the artifact or report exists.
