import { graphqlUrl } from "./uris";
import createUploadLink from "apollo-upload-client/createUploadLink.mjs";
import { ApolloClient, InMemoryCache } from "@apollo/experimental-nextjs-app-support";

/**
 * This function creates a new Apollo client instance to use in Client-Side Components (CSC).
 * 
 * The client is used to communicate with the GraphQL server
 * using the cookie-based authentication mechanism (session).
 */
export const getClientSideClient = (language?: string) => {
    return new ApolloClient({
        cache: new InMemoryCache(),
        link: createUploadLink({
            uri: graphqlUrl,
            credentials: "include",
            headers: {
                "Accept-Language": language ?? navigator.language,
            }
        }),
    });
}