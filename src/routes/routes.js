import { Fragment } from "react";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import useAuth from "../hooks/useAuth";
import Home from "../pages/Home/Home";
import Login from "../pages/Login/Login";
import Signup from "../pages/Signup/Signup";

const Private = ({ Item }) => {
  const { signed } = useAuth();
  return signed > 0 ? <Item /> : <Login />;
};

const RoutesApp = () => {
  return (
    <BrowserRouter>
      <Fragment>
        <Routes>
          <Route exact path="/home" element={<Private Item={Home} />} />
          <Route path="/" element={<Login />} />
          <Route exact path="/signup" element={<Signup />} />
          <Route path="*" element={<Login />} />
        </Routes>
      </Fragment>
    </BrowserRouter>
  );
};

export default RoutesApp;