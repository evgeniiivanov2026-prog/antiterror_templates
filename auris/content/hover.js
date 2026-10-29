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

  return { hide };
}
