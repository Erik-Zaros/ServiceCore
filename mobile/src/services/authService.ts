import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import api from './api';
import { LoginCredentials, LoginResponse } from '../interfaces/auth';

class AuthService {
    private readonly USER_KEY = 'userData';

    async login(credentials: LoginCredentials): Promise<LoginResponse> {
        try {
            const { data } = await api.post<LoginResponse>('login', {
                login: credentials.login,
                senha: credentials.senha
            });

            // Salva o login do usuário para usar depois
            await AsyncStorage.setItem('userLogin', credentials.login);

            if (data.usuario) {
                await this.setUserData(data.usuario);
            }

            return data;
        } catch (error) {
            if (axios.isAxiosError(error)) {
                const errorMessage = error.response?.data?.message || 'Erro ao fazer login. Tente novamente.';
                throw new Error(errorMessage);
            }
            throw new Error('Erro ao fazer login. Tente novamente.');
        }
    }

    async setUserData(userData: any): Promise<void> {
        try {
            await AsyncStorage.setItem(this.USER_KEY, JSON.stringify(userData));
        } catch (error) {
            console.error('Erro ao salvar dados do usuário:', error);
        }
    }

    async getUserData(): Promise<any | null> {
        try {
            const userData = await AsyncStorage.getItem(this.USER_KEY);
            return userData ? JSON.parse(userData) : null;
        } catch (error) {
            console.error('Erro ao recuperar dados do usuário:', error);
            return null;
        }
    }

    async getUserLogin(): Promise<string | null> {
        try {
            return await AsyncStorage.getItem('userLogin');
        } catch (error) {
            console.error('Erro ao recuperar login do usuário:', error);
            return null;
        }
    }

    async logout(): Promise<void> {
        try {
            await AsyncStorage.removeItem(this.USER_KEY);
            await AsyncStorage.removeItem('userLogin');
        } catch (error) {
            console.error('Erro ao fazer logout:', error);
        }
    }

    async isAuthenticated(): Promise<boolean> {
        const userData = await this.getUserData();
        return !!userData;
    }

    async buscarTecnico(login: string): Promise<any> {
        try {
            const { data } = await api.get(`tecnico/${login}`);
            return data;
        } catch (error) {
            if (axios.isAxiosError(error)) {
                const errorMessage = error.response?.data?.message || 'Erro ao buscar técnico';
                throw new Error(errorMessage);
            }
            throw new Error('Erro ao buscar técnico');
        }
    }

    async buscarTickets(params?: { data_inicio?: string; data_fim?: string }): Promise<any[]> {
        try {
            const { data } = await api.get('tickets', { params });

            // A API retorna { success: true, data: {...} }
            // Precisa retornar um array para o Dashboard
            if (data.success && data.data) {
                // Se retornar um único ticket, transforma em array
                return Array.isArray(data.data) ? data.data : [data.data];
            }

            return [];
        } catch (error) {
            if (axios.isAxiosError(error)) {
                // Se for 404 com mensagem de "nenhum ticket pendente", retorna array vazio
                if (error.response?.status === 404 &&
                    error.response?.data?.error?.message?.includes('Nenhum ticket pendente')) {
                    console.log('Nenhum ticket pendente de exportação');
                    return [];
                }

                const errorMessage = error.response?.data?.message || error.response?.data?.error?.message || 'Erro ao buscar tickets';
                throw new Error(errorMessage);
            }
            throw new Error('Erro ao buscar tickets');
        }
    }

    async buscarTicketPorId(id: number): Promise<any> {
        try {
            const { data } = await api.get(`tickets/${id}`);
            return data;
        } catch (error) {
            if (axios.isAxiosError(error)) {
                const errorMessage = error.response?.data?.message || 'Erro ao buscar ticket';
                throw new Error(errorMessage);
            }
            throw new Error('Erro ao buscar ticket');
        }
    }

    async atualizarStatusTicket(id: number, status: string): Promise<any> {
        try {
            console.log('Atualizando ticket - ID:', id, 'Status:', status);
            console.log('URL:', `tickets/${id}`);
            console.log('Body:', { status });

            const { data } = await api.put(`tickets/${id}`, { status });

            console.log('Resposta completa:', data);
            return data;
        } catch (error) {
            if (axios.isAxiosError(error)) {
                console.error('Erro Axios:', {
                    status: error.response?.status,
                    data: error.response?.data,
                    message: error.message
                });
                const errorMessage = error.response?.data?.message || 'Erro ao atualizar status do ticket';
                throw new Error(errorMessage);
            }
            console.error('Erro desconhecido:', error);
            throw new Error('Erro ao atualizar status do ticket');
        }
    }

    async marcarTicketComoExportado(id: number): Promise<any> {
        try {
            const { data } = await api.put(`tickets/${id}`, { exportado: true });
            return data;
        } catch (error) {
            if (axios.isAxiosError(error)) {
                const errorMessage = error.response?.data?.message || 'Erro ao marcar ticket como exportado';
                throw new Error(errorMessage);
            }
            throw new Error('Erro ao marcar ticket como exportado');
        }
    }
}

export const authService = new AuthService();
export { api };
