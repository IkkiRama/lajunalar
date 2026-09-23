import { ExternalLink, LayoutGrid, LogOut, Menu, ShieldX, X } from 'lucide-react';
import { useState } from 'react';
import { Link, Navigate, NavLink } from 'react-router-dom';
import { Brand, PageLoader } from './Layout';
import { useMeQuery } from '../store/api';
import { LogoutConfirmModal } from './LogoutConfirmModal';

export function AdminOnly({ children }) {
    const { data: user, isLoading } = useMeQuery();

    if (isLoading) return <PageLoader label="Memeriksa akses admin…"/>;
    if (!user) return <Navigate to="/" replace/>;
    if (user.role !== 'admin') return <div className="grid min-h-screen place-items-center bg-slate-50 px-5"><div className="card max-w-md p-8 text-center"><ShieldX className="mx-auto text-rose-500" size={46}/><h1 className="mt-4 text-2xl font-black">Akses admin diperlukan</h1><p className="mt-2 text-sm leading-6 text-slate-500">Akun ini tidak mempunyai hak untuk mengelola paket.</p><Link to="/dashboard" className="btn-primary mt-6">Kembali ke dashboard</Link></div></div>;

    return children;
}

export function AdminShell({ children, title = 'Kelola paket', description = 'Buat paket dan isi soal latihan dalam satu tempat.' }) {
    const { data: user } = useMeQuery();
    const [menuOpen, setMenuOpen] = useState(false);
    const [logoutOpen, setLogoutOpen] = useState(false);

    const navigation = <>
        <NavLink to="/kelola" end onClick={() => setMenuOpen(false)} className={({ isActive }) => `flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold transition ${isActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'}`}><LayoutGrid size={19}/>Paket & generator</NavLink>
        <a href="/admin" className="mt-2 flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"><ExternalLink size={19}/>Admin lengkap</a>
    </>;

    return <div className="min-h-screen bg-slate-50 dark:bg-slate-950">
        <header className="fixed inset-x-0 top-0 z-40 h-18 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95 lg:left-72">
            <div className="flex h-full items-center justify-between px-5 lg:px-8">
                <button onClick={() => setMenuOpen(true)} className="grid size-10 place-items-center rounded-xl border border-slate-200 lg:hidden dark:border-slate-700"><Menu size={20}/></button>
                <div className="hidden lg:block"><p className="font-black">{title}</p><p className="text-xs text-slate-500">{description}</p></div>
                <div className="ml-auto flex items-center gap-3"><div className="hidden text-right sm:block"><p className="text-sm font-bold">{user?.name}</p><p className="text-xs text-slate-500">Administrator</p></div>{user?.avatar_url ? <img src={user.avatar_url} alt="" className="size-10 rounded-xl object-cover"/> : <span className="grid size-10 place-items-center rounded-xl bg-indigo-100 font-black text-indigo-700">{user?.name?.charAt(0)}</span>}<button onClick={() => setLogoutOpen(true)} title="Keluar" className="grid size-10 place-items-center rounded-xl border border-slate-200 text-rose-600 dark:border-slate-700"><LogOut size={18}/></button></div>
            </div>
        </header>

        <aside className={`fixed inset-y-0 left-0 z-50 w-72 border-r border-slate-200 bg-white p-5 transition-transform dark:border-slate-800 dark:bg-slate-900 ${menuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'}`}>
            <div className="flex items-center justify-between"><Brand/><button onClick={() => setMenuOpen(false)} className="grid size-9 place-items-center lg:hidden"><X size={20}/></button></div>
            <div className="mt-10 rounded-2xl bg-indigo-50 p-4 dark:bg-indigo-950/40"><p className="text-xs font-black uppercase tracking-[.18em] text-indigo-600">Admin Ringkas</p><p className="mt-2 text-sm leading-5 text-slate-600 dark:text-slate-300">Khusus membuat paket dan generate soal latihan.</p></div>
            <nav className="mt-7">{navigation}</nav>
            <div className="absolute inset-x-5 bottom-5 rounded-2xl border border-slate-200 p-4 text-xs leading-5 text-slate-500 dark:border-slate-700">Butuh laporan, pengguna, dan data detail? Gunakan <a href="/admin" className="font-bold text-indigo-600">admin lengkap</a>.</div>
        </aside>
        {menuOpen && <button aria-label="Tutup menu" onClick={() => setMenuOpen(false)} className="fixed inset-0 z-40 bg-slate-950/40 lg:hidden"/>}
        <main className="min-h-screen pt-18 lg:ml-72">{children}</main>
        <LogoutConfirmModal open={logoutOpen} onClose={() => setLogoutOpen(false)}/>
    </div>;
}
