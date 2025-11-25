export interface LoginCredentials {
    login: string;
    senha: string;
}

export interface LoginResponse {
    token: string;
    usuario?: {
        id: number;
        nome: string;
        login: string;
        tipoUsuario: string;
    };
    message?: string;
}

export interface Usuario {
    id: number;
    nome: string;
    login: string;
    tipoUsuario: string;
}