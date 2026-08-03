import type { FieldValues, Path, UseFormSetError } from 'react-hook-form';
import { ApiError } from './api';

/**
 * Maps a 422 ApiError's field errors onto a react-hook-form form. Returns
 * true if the error was a validation error and was applied, false if the
 * caller should handle it another way (e.g. a generic error message).
 */
export function applyServerErrors<T extends FieldValues>(
  setError: UseFormSetError<T>,
  error: unknown,
): boolean {
  if (!(error instanceof ApiError) || !error.errors) {
    return false;
  }

  for (const [field, messages] of Object.entries(error.errors)) {
    setError(field as Path<T>, { type: 'server', message: messages[0] });
  }

  return true;
}
