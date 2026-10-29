export function clamp(value, min, max) {
  return Math.min(max, Math.max(min, value));
}

export function asUnit(value, fallback = 0.55) {
  const number = Number(value);
  if (!Number.isFinite(number)) return fallback;
  if (number > 1 && number <= 100) return clamp(number / 100, 0, 1);
  return clamp(number, 0, 1);
}
