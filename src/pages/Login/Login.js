import React, { useState } from "react";
import Input from "../../components/Input";
import Button from "../../components/Button";
import * as C from "./stylesLogin";
import { Link, useNavigate } from "react-router-dom";
import useAuth from "../../hooks/useAuth";

const Login = () => {
  const { login } = useAuth();
  const navigate = useNavigate();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!email | !password) {
      setError("Preencha todos os campos");
      return;
    }
    setLoading(true);
    setError("");

    try {
      const res = await login(email, password); // Chamar a função signin do useAuth

      if (res) {
        setError(res); // Exibir erro retornado pelo signin
      } else {
        navigate("/home"); // Redirecionar para a home em caso de sucesso
      }
    } catch (err) {
      console.error("Erro no login:", err);
      setError("Ocorreu um erro inesperado. Tente novamente.");
    } finally {
      setLoading(false); // Desativar loading
    }
  };

  return (
    <C.Container>
      <C.Label>Sistemas de Usuário - Banco de Dados II</C.Label>
      <C.Content>
        <C.Title>LOGIN</C.Title>
        <Input
          type="email"
          placeholder="Digite seu e-mail"
          value={email}
          onChange={(e) => [setEmail(e.target.value), setError("")]}
        />
        <Input
          type="password"
          placeholder="Digite sua senha"
          value={password}
          onChange={(e) => [setPassword(e.target.value), setError("")]}
        />
        <C.LabelError>{error}</C.LabelError>
        <Button 
          Text={loading ? "Carregando..." : "Entrar"} 
          onClick={handleLogin} 
          disabled={loading} // Desabilitar botão durante o loading
        />
        <C.LabelSignup>
          Não tem uma conta?
          <C.Strong>
            <Link to="/signup">&nbsp;Registre-se</Link>
          </C.Strong>
        </C.LabelSignup>
        <C.LabelForgot>
          <Link to="/forgot-password">Esqueci minha senha</Link>
        </C.LabelForgot>
      </C.Content>
    </C.Container>
  );
};

export default Login;