import dayjs from 'dayjs';
import 'dayjs/locale/pt-br';
dayjs.locale('pt-br');

type FilterFunction = (value: string | number | Date | null | undefined) => string;

const capitalizeFirst = (str: string) => str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();

const parseDate = (value: string | number | Date | null | undefined) => {
  if (!value) return null;
  const date = dayjs(value);
  return date.isValid() ? date : null;
};

const filters: Record<string, FilterFunction> = {
  formatPeriod(value) {
    if (!value) return '--';

    if (Array.isArray(value)) {
      const start = parseDate(value[0]);
      const end = parseDate(value[1]);
      const startStr = start ? capitalizeFirst(start.format('MMM YYYY')) : '--';
      const endStr = end ? capitalizeFirst(end.format('MMM YYYY')) : 'Atualmente';
      return `${startStr} - ${endStr}`;
    }

    const date = parseDate(value);

    return date ? capitalizeFirst(date.format('MMM YYYY')) : '--';
  },
};

export default filters;
