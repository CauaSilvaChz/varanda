import { Bricolage_Grotesque, Figtree } from 'next/font/google';
import { AuthProvider } from '@/lib/auth';
import { ToastProvider } from '@/lib/toast';
import './globals.css';

const bricolage = Bricolage_Grotesque({ subsets: ['latin'], variable: '--font-bricolage' });
const figtree = Figtree({ subsets: ['latin'], variable: '--font-figtree' });

export const metadata = {
  title: 'Varanda',
  description: 'Uma rede social para conversar com calma: publique, comente e acompanhe quem você segue.',
};

export default function RootLayout({ children }) {
  return (
    <html lang="pt-BR" className={`${bricolage.variable} ${figtree.variable}`}>
      <body>
        <ToastProvider>
          <AuthProvider>{children}</AuthProvider>
        </ToastProvider>
      </body>
    </html>
  );
}
