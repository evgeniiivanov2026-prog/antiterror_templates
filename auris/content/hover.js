export function attachHover(env) {
  const lens = env.createLens(env.document);
  lens.host.style.position = "fixed";
  lens.host.style.zIndex = "2147483646";
  lens.host.style.display = "none";
  lens.host.style.left = "0px";
  lens.host.style.top = "0px";
  env.document.documentElement.append(lens.host);

  let anchor = null;
  let token = 0;
  let openTimer = 0;
  let closeTimer = 0;
  let pinned = false;

  lens.onPin((next) => {
    pinned = next;
  });

  const win = env.document.defaultView;

  env.document.addEventListener("mouseover", (event) => {
    if (!env.getSettings().enabled) return;
    if (String(win.getSelection?.() || "").trim().length > 40) return;
    if (event.target === lens.host) {
      win.clearTimeout(closeTimer);
      return;
    }
    const link = event.target?.closest?.("a[href]");
    if (!link) return;
    if (link === anchor) {
      win.clearTimeout(closeTimer);
      return;
    }
    win.clearTimeout(openTimer);
    const delay = Number(env.getSettings().hoverDelay) || 260;
    openTimer = win.setTimeout(() => openFor(link), delay);
  }, true);

  env.document.addEventListener("mouseout", (event) => {
    const link = event.target?.closest?.("a[href]");
    if (!link) return;
    win.clearTimeout(openTimer);
    if (pinned) return;
    const next = event.relatedTarget;
    if (next && (next === lens.host || lens.host.contains(next))) return;
    closeTimer = win.setTimeout(() => {
      if (anchor === link) hide(false);
    }, 160);
  }, true);

  lens.host.addEventListener("mouseenter", () => win.clearTimeout(closeTimer));
  lens.host.addEventListener("mouseleave", () => {
    if (!pinned) closeTimer = win.setTimeout(() => hide(false), 140);
  });

  env.document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") hide(true);
  });

  win.addEventListener("scroll", () => {
    if (!anchor) return;
    if (pinned) place(anchor);
    else hide(false);
  }, true);

  if (win.ResizeObserver) {
    const observer = new win.ResizeObserver(() => {
      if (anchor && lens.host.style.display !== "none") place(anchor);
    });
    observer.observe(lens.host);
  }

  async function openFor(link) {
    const href = link.href;
    const anchorText = (link.innerText || link.getAttribute("aria-label") || link.title || "")
      .trim()
      .slice(0, 400);
    const settings = env.getSettings();
    if (!env.shouldInspect({ href, anchorText, pageUrl: win.location.href }, settings)) return;

    anchor = link;
    token += 1;
    const myToken = token;
    pinned = false;
    lens.resetPin();

    const local = env.evaluate({ href, anchorText });
    const pending = settings.aiMode !== "local" || settings.deepFetch;
    lens.render(env.present(local, { phase: "ready", pending }));
    lens.host.style.visibility = "hidden";
    lens.host.style.display = "block";
    place(link);
    lens.host.style.visibility = "visible";

    if (!pending) return;
    const enriched = await env.requestEnrich({ href, anchorText });
    if (myToken !== token || !enriched) {
      if (myToken === token) lens.render(env.present(local, { pending: false }));
      return;
    }
    lens.render(env.present(enriched, { pending: false }));
    place(link);
  }

  function hide(force) {
    if (pinned && !force) return;
    token += 1;
    anchor = null;
    pinned = false;
    lens.resetPin();
    lens.host.style.display = "none";
  }

  function place(link) {
    const rect = link.getBoundingClientRect();
    const card = lens.host.getBoundingClientRect();
    const gap = 12;
    let top = rect.bottom + gap;
    if (top + card.height > win.innerHeight - 8) top = rect.top - card.height - gap;
    if (top < 8) top = Math.max(8, win.innerHeight - card.height - 8);
    let left = rect.left;
    if (left + card.width > win.innerWidth - 8) left = win.innerWidth - card.width - 8;
    if (left < 8) left = 8;
    lens.host.style.left = `${Math.round(left)}px`;
    lens.host.style.top = `${Math.round(top)}px`;
  }

  async function showCheck({ mode = "page", text = "", href = "", title = "", point = null } = {}) {
    if (!env.getSettings().enabled) return;
    win.clearTimeout(openTimer);
    win.clearTimeout(closeTimer);
    token += 1;
    const myToken = token;
    anchor = null;
    lens.setPinned(true);

    const settings = env.getSettings();
    const local = env.evaluate({ href, anchorText: String(title || "").slice(0, 400) });
    const viewed = typeof env.applyText === "function" ? env.applyText(local, text, { mode }) : local;
    const length = String(text || "").trim().length;
    const pending = settings.aiMode !== "local" && length >= (mode === "page" ? 200 : 80);
    lens.render(env.present(viewed, { pending }));
    revealAt(point);
    if (!pending) return;

    const enriched = await env.requestEnrich({
      href,
      anchorText: String(title || "").slice(0, 400),
      title: title || "",
      text: String(text || "").slice(0, 8000),
      mode,
    });
    if (myToken !== token || !enriched) {
      if (myToken === token) lens.render(env.present(viewed, { pending: false }));
      return;
    }
    lens.render(env.present(enriched, { pending: false }));
  }

  function considerSelection() {
    if (!env.getSettings().enabled) return;
    const sel = win.getSelection?.();
    if (!sel || sel.isCollapsed || !sel.rangeCount) return;
    const text = String(sel).replace(/\s+/g, " ").trim();
    if (text.length < 160) return;
    if (inside(sel.anchorNode) || inside(sel.focusNode)) return;
    const parent = sel.anchorNode?.nodeType === 1 ? sel.anchorNode : sel.anchorNode?.parentElement;
    if (parent?.closest?.("input, textarea, select, [contenteditable='true']")) return;
    const rect = sel.getRangeAt(0).getBoundingClientRect();
    if (!rect || (rect.width === 0 && rect.height === 0)) return;
    showCheck({
      mode: "selection",
      text,
      href: win.location.href,
      title: env.document.title || "",
      point: { x: rect.left, y: rect.bottom },
    });
  }

  function revealAt(point) {
    lens.host.style.visibility = "hidden";
    lens.host.style.display = "block";
    const card = lens.host.getBoundingClientRect();
    const corner = !point;
    let left = corner ? win.innerWidth - card.width - 16 : point.x;
    let top = corner ? 16 : point.y + 12;
    if (!corner && top + card.height > win.innerHeight - 8) top = point.y - card.height - 12;
    if (top + card.height > win.innerHeight - 8) top = Math.max(8, win.innerHeight - card.height - 8);
    if (top < 8) top = 8;
    if (left + card.width > win.innerWidth - 8) left = win.innerWidth - card.width - 8;
    if (left < 8) left = 8;
    lens.host.style.left = `${Math.round(left)}px`;
    lens.host.style.top = `${Math.round(top)}px`;
    lens.host.style.visibility = "visible";
  }

  function inside(node) {
    if (!node) return false;
    const el = node.nodeType === 1 ? node : node.parentElement;
    if (!el) return false;
    if (el === lens.host || lens.host.contains(el)) return true;
    const root = el.getRootNode?.();
    return Boolean(root && root.host === lens.host);
  }

  return {
    hide,
    showCheck,
    considerSelection,
    owns: inside,
  };
}
