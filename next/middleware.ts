import { NextRequest, NextResponse } from 'next/server';

// Define protected and public routes
const protectedRoutes = ['/dashboard'];
const publicRoutes = ['/login', '/register', '/auth', '/'];

export function middleware(request: NextRequest) {
  const path = request.nextUrl.pathname;
  const isProtectedRoute = protectedRoutes.some(route => path.startsWith(route));

  // Check for authentication cookie
  const authCookie = request.cookies.get('turkticaret_auth');
  const isAuthenticated = authCookie?.value === 'true';

  // Redirect to /login if the user is not authenticated and trying to access protected route
  if (isProtectedRoute && !isAuthenticated) {
    return NextResponse.redirect(new URL('/auth/login', request.url));
  }

  // Redirect to /dashboard if the user is authenticated and trying to access public auth routes
  if (isAuthenticated && (path === '/auth/login' || path === '/auth/register')) {
    return NextResponse.redirect(new URL('/dashboard', request.url));
  }

  return NextResponse.next();
}

// Configure which paths the middleware should run on
export const config = {
  matcher: [
    /*
     * Match all request paths except for the ones starting with:
     * - api (API routes)
     * - _next/static (static files)
     * - _next/image (image optimization files)
     * - favicon.ico (favicon file)
     * - public folder
     */
    '/((?!api|_next/static|_next/image|favicon.ico|public).*)',
  ],
};