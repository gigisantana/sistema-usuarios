// src/pages/ForgotPassword/stylesForgotPassword.js
import styled from "styled-components";

// Reutilizando os estilos básicos que você já usa
export const Container = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  height: 100vh;
  gap: 20px; /* Aumentei um pouco o gap para o título principal */
`;

export const Content = styled.div`
  gap: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  width: 100%;
  box-shadow: 0 1px 2px #0003;
  background-color: white;
  max-width: 350px;
  padding: 20px;
  border-radius: 5px;
`;

export const Label = styled.label`
  font-size: 20px; /* Título principal maior */
  font-weight: 600;
  color: #333; /* Cor mais escura para o título principal */
  text-align: center;
`;

export const Title = styled.h2`
  font-size: 22px; /* Título interno do formulário */
  color: #676767;
  margin-bottom: 10px;
`;

export const Message = styled.p`
  font-size: 14px;
  color: ${({ type }) => (type === "error" ? "red" : "green")}; /* Mensagens coloridas */
  text-align: center;
`;

export const LinkContainer = styled.div`
  margin-top: 10px;

  a {
    font-size: 14px;
    color: #007bff; /* Cor de link padrão */
    text-decoration: none;

    &:hover {
      text-decoration: underline;
    }
  }
`;