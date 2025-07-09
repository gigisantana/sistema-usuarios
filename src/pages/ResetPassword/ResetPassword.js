// src/pages/ResetPassword/ResetPassword.js
import React, { useState, useEffect } from "react";
import { useLocation, useNavigate, Link } from "react-router-dom"; // Importar useLocation e Link
import Input from "../../components/Input";
import Button from "../../components/Button";
import * as C from "./stylesResetPassword"; // Estilos específicos
import useAuth from "../../hooks/useAuth"; // Seu hook de autenticação

const ResetPassword = () => {
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const location = useLocation(); // Hook para acessar a URL atual
  const { resetPassword } = useAuth(); // Função a ser adicionada ao useAuth

  const [token, setToken] = useState(null);

  // useEffect para extrair o token da URL quando a página carrega
  useEffect(() => {
    const queryParams = new URLSearchParams(location.search);
    const tokenFromUrl = queryParams.get("token");
    if (tokenFromUrl) {
      setToken(tokenFromUrl);
    } else {
      setMessage("Token de redefinição não encontrado na URL.");
      // Opcional: redirecionar para a página de esqueci senha ou login
      // setTimeout(() => navigate("/forgot-password"), 3000); 
    }
  }, [location.search, navigate]); // Dependências: re-executa se a URL mudar

  const handleResetPassword = async () => {
    if (!token) {
      setMessage("Token de redefinição ausente.");
      return;
    }
    if (!password || !confirmPassword) {
      setMessage("Preencha todos os campos de senha.");
      return;
    }
    if (password !== confirmPassword) {
      setMessage("As senhas não coincidem.");
      return;
    }

    setLoading(true);
    setMessage("");

    try {
      // Chama a função do AuthContext para enviar a nova senha e o token
      const res = await resetPassword(token, password); 
      
      if (res) {
        setMessage(res); // Exibe erro retornado pelo AuthContext
      } else {
        setMessage("Senha redefinida com sucesso! Você pode fazer login agora.");
        setPassword("");
        setConfirmPassword("");
        // Redireciona para a página de login após sucesso
        setTimeout(() => navigate("/"), 2000); 
      }
    } catch (err) {
      console.error("Erro ao redefinir senha:", err);
      setMessage("Ocorreu um erro inesperado ao redefinir a senha. Tente novamente.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <C.Container>
      <C.Label>Sistemas de Usuário - Banco de Dados II</C.Label> {/* Título da página */}
      <C.Content>
        <C.Title>Redefinir Senha</C.Title>
        <Input
          type="password"
          placeholder="Nova Senha"
          value={password}
          onChange={(e) => [setPassword(e.target.value), setMessage("")]}
        />
        <Input
          type="password"
          placeholder="Confirme a Nova Senha"
          value={confirmPassword}
          onChange={(e) => [setConfirmPassword(e.target.value), setMessage("")]}
        />
        <C.Message>{message}</C.Message>
        <Button 
          Text={loading ? "Redefinindo..." : "Redefinir Senha"} 
          onClick={handleResetPassword} 
          disabled={loading} 
        />
        <C.LinkContainer>
          <Link to="/">Voltar para o Login</Link>
        </C.LinkContainer>
      </C.Content>
    </C.Container>
  );
};

export default ResetPassword;