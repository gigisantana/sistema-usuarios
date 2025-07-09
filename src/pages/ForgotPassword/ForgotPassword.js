// src/pages/ForgotPassword/ForgotPassword.js
import React, { useState } from "react";
import Input from "../../components/Input"; // Reutilizando seu componente Input
import Button from "../../components/Button"; // Reutilizando seu componente Button
import * as C from "./stylesForgotPassword"; // Importando estilos específicos para esta página
import { Link } from "react-router-dom"; // Para o link de volta ao login
import useAuth from "../../hooks/useAuth"; // Seu hook de autenticação

const ForgotPassword = () => {
  const [email, setEmail] = useState("");
  const [message, setMessage] = useState(""); // Para exibir mensagens de sucesso ou erro
  const [loading, setLoading] = useState(false); // Para indicar carregamento
  const { forgotPasswordRequest } = useAuth(); // Vamos adicionar esta função ao useAuth

  const handleForgotPassword = async () => {
    if (!email) {
      setMessage("Preencha seu e-mail.");
      return;
    }

    setLoading(true);
    setMessage(""); // Limpa mensagens anteriores

    try {
      // Chama a função do AuthContext para enviar a solicitação
      const res = await forgotPasswordRequest(email); 
      
      if (res) {
        setMessage(res); // Exibe erro retornado pelo AuthContext
      } else {
        setMessage("Se o e-mail estiver cadastrado, um link de redefinição foi enviado.");
        setEmail(""); // Limpa o campo após o envio
      }
    } catch (err) {
      console.error("Erro ao solicitar redefinição:", err);
      setMessage("Ocorreu um erro inesperado. Tente novamente.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <C.Container>
      <C.Label>Sistemas de Usuário - Banco de Dados II</C.Label> {/* Título da página */}
      <C.Content>
        <C.Title>Esqueci Minha Senha</C.Title>
        <Input
          type="email"
          placeholder="Digite seu e-mail cadastrado"
          value={email}
          onChange={(e) => [setEmail(e.target.value), setMessage("")]}
        />
        <C.Message>{message}</C.Message> {/* Exibe mensagens */}
        <Button 
          Text={loading ? "Enviando..." : "Enviar Link de Redefinição"} 
          onClick={handleForgotPassword} 
          disabled={loading} 
        />
        <C.LinkContainer>
          <Link to="/">Lembrei minha senha</Link>
        </C.LinkContainer>
      </C.Content>
    </C.Container>
  );
};

export default ForgotPassword;