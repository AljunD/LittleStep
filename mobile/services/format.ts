/** Formats "YYYY-MM-DD" as a local calendar date (no timezone shift) and full ISO timestamps as-is. */
export function formatDate(value: string | null | undefined): string | null {
  if (!value) {
    return null;
  }

  const dateOnly = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
  const date = dateOnly
    ? new Date(Number(dateOnly[1]), Number(dateOnly[2]) - 1, Number(dateOnly[3]))
    : new Date(value);

  return Number.isNaN(date.getTime())
    ? null
    : date.toLocaleDateString(undefined, { month: "short", day: "numeric", year: "numeric" });
}

type NamedPerson = {
  first_name: string;
  middle_name?: string | null;
  last_name: string;
};

export function fullName(person: NamedPerson | null | undefined): string {
  return person
    ? [person.first_name, person.middle_name, person.last_name].filter(Boolean).join(" ")
    : "";
}

/** Shows "—" for empty values so every field row has visible content. */
export function displayValue(value: string | number | null | undefined): string {
  return value === null || value === undefined || String(value).trim() === ""
    ? "—"
    : String(value);
}
