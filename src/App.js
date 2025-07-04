import RoutesApp from "./routes/routes";
import { AuthProvider } from "./contexts/auth";
import GlobalStyle from "./styles/global";

const App = () => (
  <AuthProvider>
    <RoutesApp />
    <GlobalStyle />
  </AuthProvider>
);

export default App;