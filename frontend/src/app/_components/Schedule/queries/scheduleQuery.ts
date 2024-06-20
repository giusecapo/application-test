
import { gql } from '@apollo/client';

const scheduleQuery = gql`
query (
    $limit: Int!,
    $offset: Int!,
    $startDate: String!
) {
    eventsSubset(
        limit: $limit,
        offset: $offset,
        sort: { sortBy: "date", sortDirection: ASC },
        filters:[{
            field: "date",
            value: $startDate,
            operator: GT,
            valueType: DATETIME
        }]
    ) {
        items {
            id
            name
            date
            program {
                speaker
            }
        }
    }        
}`;

export default scheduleQuery;