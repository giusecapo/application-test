import { headers } from 'next/headers'
import { ApolloClient, HttpLink, InMemoryCache } from "@apollo/client";
import { registerApolloClient } from "@apollo/experimental-nextjs-app-support/rsc";
import { graphqlUrl } from "./uris";

/**
 * This function creates a new Apollo client instance to use in Server-Side Components (SSC).
 * 
 * The client is used to communicate with the GraphQL server
 * using the cookie-based authentication mechanism (session).
 * 
 * Requests performed with this client are therefore
 * authenticated and authorized based on the user's session.
 * 
 * Use this client for all requests that require the user's permissions to be checked.
 */
export const { getClient } = registerApolloClient(() => {
    const headersInstance = headers();
    return new ApolloClient({
        cache: new InMemoryCache(),
        defaultOptions: {
            // The error policy "all" avoids that an exception is thrown when a GraphQL error occurs.
            // Instead, the error is added to the result object. This is necessary in our case because
            // we want to handle the error in the UI and also be able to display partial data.
            query: { errorPolicy: "all", fetchPolicy: "no-cache" },
            mutate: { errorPolicy: "all", fetchPolicy: "no-cache" },
            watchQuery: { errorPolicy: "all", fetchPolicy: "no-cache" },
        },
        link: new HttpLink({
            uri: graphqlUrl,
            credentials: "include",
            headers: {
                "Accept-Language": headersInstance.get("accept-language") ?? "",
                //Forward cookies to the server
                cookie: headersInstance.get("cookie") ?? ""
            },
        }),
    });
});
