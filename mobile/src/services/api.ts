import axios from 'axios';
import { API_URL, API_KEY } from '../config/constants';

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'X-API-KEY': API_KEY,
    }
});

export default api;