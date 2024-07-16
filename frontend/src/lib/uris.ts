// @giusecapo: need to check if uri is needed for client side or server side API call. If server-side, docker container name is the correct url. Dev only.
export const isServer = () => typeof window === `undefined`;

const defaultUrl: string = process.env.NEXT_PUBLIC_DEFAULT_URL ?? "";
const graphqlUrl: string = isServer() ? process.env.NEXT_PUBLIC_GRAPHQL_DOCKER_URL : process.env.NEXT_PUBLIC_GRAPHQL_URL;

export {
    defaultUrl,
    graphqlUrl,
}
