import axios from 'axios';
import type { ApiResponse } from '@/types';

/**
 * Extrai a mensagem de erro da resposta da API de acordo com a estrutura padrão
 * documentada em api/docs/API.md ({ code, status, message, data?, errors? }).
 *
 * @param error Objeto de erro capturado
 * @param fallbackMessage Mensagem padrão caso não seja possível obter da resposta
 * @returns Mensagem de erro da resposta da API
 */
export function getApiErrorMessage(error: unknown, fallbackMessage = 'Ocorreu um erro inesperado'): string {
  if (axios.isAxiosError<ApiResponse>(error)) {
    const responseData = error.response?.data;

    if (responseData) {
      // Se houver erros específicos de validação por campo, anexa à mensagem do endpoint
      if (responseData.errors && typeof responseData.errors === 'object') {
        const errorDetails = Object.values(responseData.errors).filter(Boolean);
        if (errorDetails.length > 0) {
          const details = errorDetails.join(', ');
          if (responseData.message && typeof responseData.message === 'string') {
            const separator = responseData.message.trim().endsWith('.') ? ' ' : ': ';
            return `${responseData.message.trim()}${separator}${details}`;
          }
          return details;
        }
      }

      // Mensagem principal retornada no envelope da resposta da API
      if (responseData.message && typeof responseData.message === 'string') {
        return responseData.message;
      }
    }

    if (error.message && typeof error.message === 'string') {
      return error.message;
    }
  } else if (error instanceof Error && error.message) {
    return error.message;
  }

  return fallbackMessage;
}
