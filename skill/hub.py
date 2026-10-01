#!/usr/bin/env python3
"""
Skill Hub: Unified CLI for Open Design & UI/UX Agent Skill System.
Provides fast search across 70+ agent skills, 150+ brand design systems,
110+ rendering templates, and 67+ UI styles.
"""

import sys
import os
import argparse
import subprocess
from pathlib import Path

SKILL_DIR = Path(__file__).resolve().parent
PROJECT_ROOT = SKILL_DIR.parent
UIUX_DIR = SKILL_DIR / "ui-ux-agent-skill-system"
UIUX_PRO_MAX = UIUX_DIR / "core" / "skills" / "ui-ux-pro-max"
OPEN_DESIGN_DIR = SKILL_DIR / "open-design-skill"
OPEN_DESIGN_ROOT = Path(os.environ.get("OPEN_DESIGN_ROOT", Path.home() / ".open-design-skill" / "repo"))

SKILL_TIERS = {
    "1. Main Control & Orchestration": [
        ("senior-ui-ux-orchestrator", "Central chair and conflict resolver for all UI/UX tasks"),
        ("task-plan-v2-orchestrator", "Task plan governance, multi-step phases, handoff contracts"),
        ("workflow-compliance-supervisor", "Verifies real artifacts against promised journeys"),
        ("agent-progress-visualizer", "Generates real-time visual progress cockpit & HTML screens"),
    ],
    "2. Design Intelligence & Exploration": [
        ("ui-ux-pro-max", "67 styles, 161 palettes, 57 font pairings, 161 product reasoning rules"),
        ("visual-content-director", "Visual art direction, imagery, 3D, and layout mood"),
        ("stitch-design-bridge", "Google Stitch / AI UI variant exploration bridge"),
        ("pencil-design-bridge", "AI wireframing and sketching bridge"),
        ("design-critic-skill", "Visual hierarchy, taste, polish, typography, and anti-slop review"),
        ("ux-audit-skill", "Evidence-backed heuristic audit (Nielsen-Norman, WCAG)"),
    ],
    "3. Open Design Substrate": [
        ("open-design", "150+ brand DESIGN.md specs, 110+ templates, 11 universal craft rules"),
    ],
    "4. Implementation Specialists": [
        ("webapp-ui-skill", "SaaS dashboards, admin panels, CRM, tables, filters, state matrix"),
        ("marketing-site-skill", "High-converting landing pages, marketing sites, product launches"),
        ("admin-ui-orchestrator", "Enterprise admin systems, complex data grids"),
        ("admin-ui-builder", "Admin console component and screen builder"),
        ("site-ai-assistant-builder", "Chat widgets, prompt input interfaces, streaming UI"),
        ("omnichannel-comms-builder", "Notification centers, email templates, toast messages"),
    ],
    "5. Figma Subsystem": [
        ("senior-figma-orchestrator", "Central coordinator for all Figma MCP & API operations"),
        ("figma-context-reader", "Reads frames, components, and variables from Figma files"),
        ("figma-design-to-code-bridge", "Translates Figma designs into clean React/Tailwind/HTML"),
        ("figma-code-to-canvas", "Pushes code components onto the Figma canvas"),
        ("figma-canvas-editor", "Manipulates auto-layout, typography, and styles in Figma"),
        ("figma-design-system-sync", "Syncs Figma variables/tokens with codebase CSS/tokens"),
        ("figma-assets-manager", "Exports and optimizes SVG/WebP assets from Figma"),
        ("figma-apply-effects", "Generates Figma blur, shadows, and blend modes"),
        ("figma-workflow-auditor", "Audits layer naming, auto-layout, and token hygiene"),
    ],
    "6. Interactive & Visual Effects": [
        ("cursor-reveal-hero", "Interactive cursor masking, hover-reveal, spotlight effects"),
        ("image-layer-alignment-validator", "Checks base/reveal image drift and layer alignment"),
        ("website-to-hyperframes", "Converts web pages into animated interactive hyperframes"),
    ],
    "7. Product & SEO Architecture": [
        ("ui-ux-llm-product-architect", "Information architecture, user flows, responsive layouts"),
        ("ux-journey-architect", "User journey mapping, friction reduction, drop-off analysis"),
        ("concept-prototyper", "Rapid interactive prototyping in code or mockups"),
        ("seo-llm-site-architect", "Architecture optimized for Google & LLM knowledge engines"),
        ("semantic-core-builder", "Semantic core, keyword clusters, search intent mapping"),
        ("technical-seo-schema-engineer", "JSON-LD structured data, Schema.org entities"),
        ("llm-friendly-site-architect", "Optimizes site structure for Perplexity/ChatGPT/Gemini"),
        ("llm-citation-monitor", "Monitors brand citations in LLM answer engines"),
    ],
    "8. Infrastructure, Launch & Analytics": [
        ("tech-stack-selector", "Selects optimal frontend/backend stack for project goals"),
        ("analytics-setup-architect", "Event tracking schemas (GA4, PostHog, Mixpanel)"),
        ("paid-traffic-architect", "PPC landing page optimization & UTM architecture"),
        ("deploy-orchestrator", "Deployment pipeline, static hosting, CDN configuration"),
        ("web-security-architect", "CSP, security headers, XSS/CSRF hardening"),
        ("launch-readiness-auditor", "Pre-flight launch checklist and readiness verification"),
    ],
}


def cmd_search(args):
    """Run UI Pro Max search"""
    search_script = UIUX_PRO_MAX / "scripts" / "search.py"
    if not search_script.exists():
        print(f"Error: {search_script} not found")
        return 1

    cmd = [sys.executable, str(search_script), args.query]
    if args.domain:
        cmd.extend(["--domain", args.domain])
    if args.stack:
        cmd.extend(["--stack", args.stack])
    if args.max_results:
        cmd.extend(["--max-results", str(args.max_results)])
    if args.json:
        cmd.append("--json")
    if args.design_system:
        cmd.append("--design-system")

    subprocess.run(cmd)
    return 0


def cmd_list_skills(args):
    """List all available skills organized by functional tier"""
    print("=" * 80)
    print("  UI/UX & DESIGN SKILL SYSTEM - 72 AVAILABLE SKILLS")
    print("=" * 80)

    category_filter = args.category.lower() if args.category else None

    for tier, skills in SKILL_TIERS.items():
        if category_filter and category_filter not in tier.lower():
            # Check if any skill in tier matches
            matching = [s for s in skills if category_filter in s[0].lower() or category_filter in s[1].lower()]
            if not matching:
                continue
            skills = matching

        print(f"\n\033[1;36m▶ {tier}\033[0m")
        for name, desc in skills:
            print(f"  • \033[1;32m{name:<34}\033[0m : {desc}")
    print()


def cmd_open_design(args):
    """Query Open Design catalogue (systems, templates, craft)"""
    action = args.subaction

    if action == "systems":
        script = OPEN_DESIGN_DIR / "scripts" / "list-design-systems.mjs"
        proc = subprocess.run(["node", str(script)], capture_output=True, text=True)
        if proc.returncode != 0:
            print(proc.stderr)
            return proc.returncode
        lines = proc.stdout.splitlines()
        header = lines[0].split("\t")
        print(f"\033[1;34m{'SLUG':<20} {'CATEGORY':<25} {'TITLE':<30}\033[0m")
        print("-" * 80)
        q = args.filter.lower() if args.filter else ""
        count = 0
        for line in lines[1:]:
            parts = line.split("\t")
            if len(parts) >= 3:
                slug, title, cat = parts[0], parts[1], parts[2]
                desc = parts[3] if len(parts) > 3 else ""
                if not q or q in slug.lower() or q in title.lower() or q in cat.lower() or q in desc.lower():
                    print(f"{slug:<20} {cat[:24]:<25} {title[:28]:<30}")
                    count += 1
        print(f"\nTotal matching design systems: {count}")

    elif action == "templates":
        script = OPEN_DESIGN_DIR / "scripts" / "list-design-templates.mjs"
        proc = subprocess.run(["node", str(script)], capture_output=True, text=True)
        if proc.returncode != 0:
            print(proc.stderr)
            return proc.returncode
        lines = proc.stdout.splitlines()
        print(f"\033[1;34m{'SLUG':<24} {'MODE':<12} {'DESCRIPTION':<40}\033[0m")
        print("-" * 80)
        q = args.filter.lower() if args.filter else ""
        count = 0
        for line in lines[1:]:
            parts = line.split("\t")
            if len(parts) >= 6:
                slug, mode, desc = parts[0], parts[1], parts[5]
                if not q or q in slug.lower() or q in mode.lower() or q in desc.lower():
                    print(f"{slug:<24} {mode:<12} {desc[:42]:<40}")
                    count += 1
        print(f"\nTotal matching templates: {count}")

    elif action == "craft":
        script = OPEN_DESIGN_DIR / "scripts" / "list-craft.mjs"
        proc = subprocess.run(["node", str(script)], capture_output=True, text=True)
        if proc.returncode != 0:
            print(proc.stderr)
            return proc.returncode
        lines = proc.stdout.splitlines()
        print(f"\033[1;34m{'SLUG':<30} {'TITLE':<45}\033[0m")
        print("-" * 80)
        for line in lines[1:]:
            parts = line.split("\t")
            if len(parts) >= 2:
                print(f"{parts[0]:<30} {parts[1]:<45}")

    elif action == "bind":
        if not args.system or not args.template:
            print("Error: Both --system <slug> and --template <slug> are required for bind.")
            return 1
        import json
        from datetime import datetime, timezone
        binding = {
            "version": 1,
            "designSystem": {
                "slug": args.system,
                "path": f"design-systems/{args.system}"
            },
            "skill": {
                "slug": args.template,
                "path": f"design-templates/{args.template}",
                "kind": "design-template",
                "mode": "prototype"
            },
            "boundAt": datetime.now(timezone.utc).isoformat()
        }
        target = PROJECT_ROOT / ".open-design.json"
        target.write_text(json.dumps(binding, indent=2), encoding="utf-8")
        print(f"✓ Bound design system '{args.system}' + template '{args.template}' to {target}")
    return 0


def cmd_view_board(args):
    """Show paths to interactive dashboards and review boards"""
    board_path = UIUX_DIR / "docs" / "skill-review-board.html"
    graph_path = UIUX_DIR / "docs" / "skill-graph.html"
    task_path = UIUX_DIR / "docs" / "task-dashboard.html"

    print("=" * 80)
    print("  INTERACTIVE VISUAL TOOLS")
    print("=" * 80)
    print(f"\n1. Skill Review Board (Full interactive browser of all skills & details):")
    print(f"   file://{board_path}")
    print(f"\n2. Skill Graph (Visual dependency and routing relationship map):")
    print(f"   file://{graph_path}")
    print(f"\n3. Task Dashboard (Task governance and status viewer):")
    print(f"   file://{task_path}\n")


def main():
    parser = argparse.ArgumentParser(
        description="Unified UI/UX & Design Skill System Hub",
        formatter_class=argparse.RawDescriptionHelpFormatter
    )
    subparsers = parser.add_subparsers(dest="command", help="Command to run")

    # search
    p_search = subparsers.add_parser("search", help="Search UI Pro Max design intelligence")
    p_search.add_argument("query", help="Query (e.g. 'dashboard', 'dark mode', 'fintech')")
    p_search.add_argument("--domain", "-d", choices=["style", "color", "chart", "landing", "product", "ux", "typography", "icons", "react", "web", "google-fonts"])
    p_search.add_argument("--stack", "-s", choices=["react", "nextjs", "vue", "svelte", "astro", "swiftui", "react-native", "flutter", "html-tailwind", "shadcn", "threejs", "angular", "laravel"])
    p_search.add_argument("--max-results", "-n", type=int, default=3)
    p_search.add_argument("--json", action="store_true")
    p_search.add_argument("--design-system", "-ds", action="store_true")

    # skills
    p_skills = subparsers.add_parser("skills", help="List all 72 skills grouped by category")
    p_skills.add_argument("category", nargs="?", help="Optional filter by tier name or skill name")

    # open-design
    p_od = subparsers.add_parser("open-design", help="Browse Open Design systems, templates & craft")
    p_od.add_argument("subaction", choices=["systems", "templates", "craft", "bind"], help="Sub-action to run")
    p_od.add_argument("--filter", "-f", help="Filter by keyword (e.g. 'dark', 'stripe', 'deck')")
    p_od.add_argument("--system", "-s", help="Design system slug to bind (e.g. 'stripe')")
    p_od.add_argument("--template", "-t", help="Template slug to bind (e.g. 'dashboard')")

    # view-board
    subparsers.add_parser("view-board", help="Get link to interactive HTML visual review boards")

    args = parser.parse_args()

    if not args.command:
        parser.print_help()
        print("\n\033[1;33mQuick Examples:\033[0m")
        print("  python3 skill/hub.py skills")
        print("  python3 skill/hub.py search 'dashboard' --domain product")
        print("  python3 skill/hub.py search 'dark mode' --domain style")
        print("  python3 skill/hub.py open-design systems --filter tech")
        print("  python3 skill/hub.py open-design templates --filter dashboard")
        print("  python3 skill/hub.py view-board")
        return 0

    if args.command == "search":
        return cmd_search(args)
    elif args.command == "skills":
        return cmd_list_skills(args)
    elif args.command == "open-design":
        return cmd_open_design(args)
    elif args.command == "view-board":
        return cmd_view_board(args)


if __name__ == "__main__":
    sys.exit(main() or 0)
