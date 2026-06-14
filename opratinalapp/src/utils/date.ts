export function getISTDateString(dateInput: string | Date = new Date()): string {
  const date = new Date(dateInput);
  return date.toLocaleDateString('en-IN', { timeZone: 'Asia/Kolkata' });
}

export function getISTTimeString(dateInput: string | Date = new Date()): string {
  const date = new Date(dateInput);
  return date.toLocaleTimeString('en-IN', { timeZone: 'Asia/Kolkata', hour: '2-digit', minute: '2-digit' });
}

export function getISTDateTimeString(dateInput: string | Date = new Date()): string {
  return `${getISTDateString(dateInput)} ${getISTTimeString(dateInput)}`;
}
