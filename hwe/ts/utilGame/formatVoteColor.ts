import _colorNames from 'css-color-names';

const colorNames = _colorNames as Record<string, string>;

const colors: string[] = [
  "red", "orange", "yellow", "green", "blue", "navy", "purple"
].map(color => colorNames[color]);

export function formatVoteColor(type: number): string {
  return colors[type % colors.length];
}
