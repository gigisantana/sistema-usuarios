import { createContext, useEffect, useState } from "react";
import axios from "axios"; // Importe o Axios aqui

export const AuthContext = createContext({});

// --- URL do SEU NOVO BACKEND PHP ---
// Este é o endereço onde o seu serviço 'app_backend' no Docker Compose está disponível.
// Se o frontend e o backend estiverem no Docker Compose e o nome do serviço do backend for 'app_backend',
// eles podem se comunicar usando o nome do serviço como hostname.
const BACKEND_URL = "http://localhost:8000"; // <--- ESTE É O URL DO SEU BACKEND PHP NO DOCKER!
                                            // A porta '80' é a porta interna do contêiner do seu backend.

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null); // Melhor inicializar com null para 'nenhum usuário logado'

  // O useEffect para verificar o login ao carregar a página
  useEffect(() => {
    const userToken = localStorage.getItem("user_token");
    if (userToken) {
      // Se houver um token, você pode tentar "hidratar" o usuário.
      // Em um sistema real, você faria uma chamada ao seu backend para validar o token
      // e buscar os dados do usuário, garantindo que o token ainda é válido.
      // Por simplicidade aqui, vamos apenas pegar o email do token armazenado.
      const parsedToken = JSON.parse(userToken);
      if (parsedToken && parsedToken.email) {
        setUser({ email: parsedToken.email });
      }
    }
  }, []); // Array de dependências vazio para rodar apenas na montagem

  // --- Função de Login (login) ---
  const login = async (email, password) => { // Torne a função assíncrona
    try {
      // Remove o uso de localStorage.getItem("users_bd")
      // Chama o endpoint de login do SEU NOVO BACKEND PHP
      const response = await axios.post(`${BACKEND_URL}/login.php`, {
        email,
        password,
      });

      // Supondo que seu backend PHP de login retorne um token e os dados do usuário
      const { token, user: userData } = response.data;

      if (token && userData) {
        // Armazena o token e o email do usuário no localStorage
        localStorage.setItem("user_token", JSON.stringify({ email: userData.email, token }));
        setUser(userData); // Define o usuário no estado do contexto
        return null; // Retorna null para indicar sucesso (sem mensagem de erro)
      } else {
        // Caso a resposta não tenha token ou userData, mas a requisição foi 2xx
        return response.data?.message || "Erro inesperado ao fazer login.";
      }
    } catch (error) {
      // Captura erros da requisição (rede, 4xx, 5xx)
      console.error("Erro no login:", error.response?.data || error.message);
      // Retorna a mensagem de erro do backend, ou uma genérica
      return error.response?.data?.message || "E-mail ou senha incorretos.";
    }
  };

  // --- Função de Registro (signup) ---
  const signup = async (email, password) => { // Torne a função assíncrona
    try {
      // Remove o uso de localStorage.getItem("users_bd") para verificação de usuário existente
      // Essa verificação agora será feita pelo seu backend PHP.

      // Chama o endpoint de registro do SEU NOVO BACKEND PHP
      const response = await axios.post(`${BACKEND_URL}/register.php`, {
        email,
        password,
      });

      // Supondo que seu backend PHP de registro retorne um status 201 (Created) para sucesso
      if (response.status === 201) {
        // Após o registro bem-sucedido no backend, o usuário não é logado automaticamente.
        // Ele precisará usar a função login para fazer o login.
        return null; // Retorna null para indicar sucesso (sem mensagem de erro)
      } else {
        // Caso a API retorne um status 2xx, mas com uma mensagem de erro no corpo
        return response.data?.message || "Erro desconhecido ao registrar.";
      }
    } catch (error) {
      // Captura erros da requisição (rede, 4xx, 5xx)
      console.error("Erro no registro:", error.response?.data || error.message);
      // Retorna a mensagem de erro do backend, ou uma genérica
      return error.response?.data?.message || "Erro ao registrar. Tente novamente.";
    }
  };

  // --- Função de Logout (signout) ---
  const signout = () => {
    setUser(null); // Limpa o estado do usuário
    localStorage.removeItem("user_token"); // Remove o token do localStorage
    // Se você tiver um endpoint de logout no backend para invalidar o token, chame-o aqui.
  };

  return (
    <AuthContext.Provider
      value={{ user, signed: !!user, login, signup, signout }}
    >
      {children}
    </AuthContext.Provider>
  );
};