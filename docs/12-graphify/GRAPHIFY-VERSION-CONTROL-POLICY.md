---
id: DOC-GRAPHIFY-VERSION-CONTROL-POLICY
title: "Graphify Version Control Policy"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-13
---

# Graphify Version Control Policy

## Audit Summary

Dilaksanakan pada 2026-08-13 sebagai bagian dari Phase 5B2 Final Repository Cleanup.

| Metric | Value |
|--------|-------|
| Total graphify-out/ size | 174MB |
| cache/ size | 147MB (10,698 files) |
| Canonical output size | ~8MB (graph.json + graph.html + GRAPH_REPORT.md + manifest.json) |
| Files tracked in git | 10,722 |
| Historical snapshots | 5 (2026-08-09 through 2026-08-13) |

---

## Classification: KEEP-PARTIAL

Berdasarkan analisis reproducibility dan utility:

| Path | Jenis | Reproducible? | Policy |
|------|-------|---------------|--------|
| `graphify-out/graph.json` | Graph data utama | Ya (via `graphify update .`) | TRACK |
| `graphify-out/graph.html` | Visualisasi interaktif | Ya | TRACK |
| `graphify-out/GRAPH_REPORT.md` | Report teks | Ya | TRACK |
| `graphify-out/manifest.json` | Metadata | Ya | TRACK |
| `graphify-out/.graphify_labels.json` | Curated community labels | SEBAGIAN (perlu LLM) | TRACK |
| `graphify-out/.graphify_labels.json.sig` | Signature | Ya | TRACK |
| `graphify-out/.graphify_root` | Root marker | Ya | TRACK |
| `graphify-out/YYYY-MM-DD/` | Historical snapshots | Ya (dari git history) | TRACK |
| `graphify-out/cache/` | AST extraction cache | 100% reproducible | IGNORE |

### Reasoning

**TRACK canonical outputs**:
- `graph.json`, `graph.html`, `GRAPH_REPORT.md` adalah deliverables utama yang dibaca AI agents untuk query codebase.
- `.graphify_labels.json` mengandung curated community labels yang membutuhkan LLM dan tidak sepenuhnya reproducible.
- Historical snapshots memberikan audit trail perubahan arsitektur yang berharga.

**IGNORE cache**:
- `graphify-out/cache/` adalah 100% pure derivat — AST JSON yang di-extract dari source files.
- Ukurannya 147MB dengan 10,698 files — sangat noise bagi git history.
- Di-regenerate otomatis setiap kali `graphify update .` dijalankan.

---

## Applied Policy

```gitignore
# Graphify cache — reproducible, not tracked
graphify-out/cache/
```

Entry ini ditambahkan ke `.gitignore` root, dan cache di-remove dari git index.

---

## Regeneration Command

Jika graphify-out perlu di-rebuild dari nol:

```bash
# Rebuild graph (AST only, tidak perlu API key)
graphify update .

# Rebuild dengan semantic extraction (perlu GEMINI_API_KEY)
GEMINI_API_KEY=... graphify update .

# Rebuild labels saja
graphify label
```

---

## Maintenance

Setiap commit yang mengubah source code secara signifikan (file baru, refactor besar):

```bash
graphify update .
git add graphify-out/graph.json graphify-out/graph.html graphify-out/GRAPH_REPORT.md graphify-out/manifest.json graphify-out/.graphify_labels.json
git commit -m "chore(graphify): update knowledge graph"
```

Cache TIDAK perlu di-add ke git.
