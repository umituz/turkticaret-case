import { withAuth } from "next-auth/middleware"

export default withAuth(
  function middleware(req) {
    // Additional middleware logic can go here
  },
  {
    callbacks: {
      authorized: ({ token, req }) => {
        // Check if user is authenticated
        if (!token) {
          // Allow access to public routes
          const { pathname } = req.nextUrl
          return (
            pathname.startsWith('/auth') ||
            pathname === '/' ||
            pathname.startsWith('/api/auth')
          )
        }
        
        // Allow all authenticated users
        return true
      },
    },
  }
)

export const config = {
  matcher: ["/((?!api/auth|_next/static|_next/image|favicon.ico).*)"],
}