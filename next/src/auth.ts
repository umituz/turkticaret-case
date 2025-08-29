import NextAuth, { Session, User } from "next-auth"
import { JWT } from "next-auth/jwt"
import Credentials from "next-auth/providers/credentials"
import { authService } from "@/services/authService"

const authOptions = {
  providers: [
    Credentials({
      id: "credentials",
      name: "credentials",
      credentials: {
        email: { label: "Email", type: "email" },
        password: { label: "Password", type: "password" }
      },
      async authorize(credentials) {
        try {
          if (!credentials?.email || !credentials?.password) {
            return null;
          }

          const response = await authService.login({
            email: credentials.email as string,
            password: credentials.password as string,
          });
          
          if (response.success && response.data.user) {
            return {
              id: response.data.user.uuid,
              email: response.data.user.email,
              name: response.data.user.name,
              role: response.data.user.role || "user",
              accessToken: response.data.token,
            };
          }
          return null;
        } catch (error) {
          console.error("Auth error:", error);
          return null;
        }
      },
    }),
  ],
  pages: {
    signIn: "/auth/login",
  },
  callbacks: {
    session: async ({ session, token }: { session: Session, token: JWT }) => {
      if (token) {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        (session.user as any).id = token.sub as string;
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        (session.user as any).role = (token as any).role as string;
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        (session.user as any).accessToken = (token as any).accessToken as string;
      }
      return session
    },
    jwt: async ({ token, user }: { token: JWT, user: User }) => {
      if (user) {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        (token as any).role = (user as any).role;
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        (token as any).accessToken = (user as any).accessToken;
      }
      return token
    },
  },
  session: {
    strategy: "jwt" as const,
    maxAge: 24 * 60 * 60, // 24 hours
  },
}

export { authOptions }
export default NextAuth(authOptions)