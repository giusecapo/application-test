import { NextSSRApolloClient, NextSSRInMemoryCache } from "@apollo/experimental-nextjs-app-support/ssr";
import { graphqlUrl } from "./uris";
import createUploadLink from "apollo-upload-client/createUploadLink.mjs";

/**
 * This function creates a new Apollo client instance to use in Client-Side Components (CSC).
 * 
 * The client is used to communicate with the GraphQL server
 * using the cookie-based authentication mechanism (session).
 */
export const getClientSideClient = (language?: string) => {
    return new NextSSRApolloClient({
        cache: new NextSSRInMemoryCache(),
        link: createUploadLink({
            uri: graphqlUrl,
            credentials: "include",
            headers: {
                "Accept-Language": language ?? navigator.language,
            }
        }),
    });
}