import React from 'react';
import { createRoot } from 'react-dom/client';
import { Provider } from 'react-redux';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { store } from './store';
import { Protected } from './components/Layout';
import { AdminOnly } from './components/AdminLayout';
import { LandingPage } from './pages/LandingPage';
import { DashboardPage, PackagesPage, QuizDetailPage } from './pages/DashboardPages';
import { HistoryPage } from './pages/HistoryPage';
import { AttemptPage, ResultPage } from './pages/AttemptPages';
import { AdminPackagesPage } from './pages/AdminPages';
import { GenerationProgressPage, GenerationSuccessPage } from './pages/GenerationPages';

function App() {
    return <BrowserRouter><Routes>
        <Route path="/" element={<LandingPage/>}/>
        <Route path="/dashboard" element={<Protected><DashboardPage/></Protected>}/>
        <Route path="/paket" element={<Protected><PackagesPage/></Protected>}/>
        <Route path="/riwayat" element={<Protected><HistoryPage/></Protected>}/>
        <Route path="/paket/:uuid" element={<Protected><QuizDetailPage/></Protected>}/>
        <Route path="/attempt/:uuid" element={<Protected><AttemptPage/></Protected>}/>
        <Route path="/attempt/:uuid/hasil" element={<Protected><ResultPage/></Protected>}/>
        <Route path="/kelola" element={<Protected><AdminOnly><AdminPackagesPage/></AdminOnly></Protected>}/>
        <Route path="/kelola/generator/:uuid" element={<Protected><AdminOnly><GenerationProgressPage/></AdminOnly></Protected>}/>
        <Route path="/kelola/generator/:uuid/sukses" element={<Protected><AdminOnly><GenerationSuccessPage/></AdminOnly></Protected>}/>
        <Route path="*" element={<Navigate to="/" replace/>}/>
    </Routes></BrowserRouter>;
}

createRoot(document.getElementById('app')).render(<React.StrictMode><Provider store={store}><App/></Provider></React.StrictMode>);
