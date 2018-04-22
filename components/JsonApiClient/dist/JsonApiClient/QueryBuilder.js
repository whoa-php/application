"use strict";
/**
 * Copyright 2015-2017 info@neomerx.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
class QueryBuilder {
    constructor(type) {
        this.isEncodeUriEnabled = true;
        this.type = type;
    }
    onlyFields(...fields) {
        this.fields = fields;
        return this;
    }
    withFilters(...filters) {
        this.filters = filters;
        return this;
    }
    withSorts(...sorts) {
        this.sorts = sorts;
        return this;
    }
    withIncludes(...relationships) {
        this.includes = relationships;
        return this;
    }
    withPagination(offset, limit) {
        offset = Math.max(-1, Math.floor(offset));
        limit = Math.max(0, Math.floor(limit));
        if (offset >= 0 && limit > 0) {
            this.offset = offset;
            this.limit = limit;
        }
        else {
            this.offset = undefined;
            this.limit = undefined;
        }
        return this;
    }
    enableEncodeUri() {
        this.isEncodeUriEnabled = true;
        return this;
    }
    disableEncodeUri() {
        this.isEncodeUriEnabled = false;
        return this;
    }
    isUriEncodingEnabled() {
        return this.isEncodeUriEnabled;
    }
    read(index, relationship) {
        const relationshipTail = relationship === undefined ? `/${index}` : `/${index}/${relationship}`;
        const result = `/${this.type}${relationshipTail}${this.buildParameters(false)}`;
        return this.isUriEncodingEnabled() === true ? encodeURI(result) : result;
    }
    index() {
        const result = `/${this.type}${this.buildParameters(true)}`;
        return this.isUriEncodingEnabled() === true ? encodeURI(result) : result;
    }
    /**
     * @internal
     */
    buildParameters(isIncludeNonFields) {
        let params = null;
        // add field params to get URL like '/articles?include=author&fields[articles]=title,body&fields[people]=name'
        // see http://jsonapi.org/format/#fetching-sparse-fieldsets
        if (this.fields !== undefined && this.fields.length > 0) {
            let fieldsResult = '';
            for (let field of this.fields) {
                const curResult = `fields[${field.type}]=${QueryBuilder.separateByComma(field.fields)}`;
                fieldsResult = fieldsResult.length === 0 ? curResult : `${fieldsResult}&${curResult}`;
            }
            params = fieldsResult;
        }
        // add filter parameters to get URL like 'filter[id][greater-than]=10&filter[id][less-than]=20&filter[title][like]=%Typ%'
        // note: the spec do not specify format for filters http://jsonapi.org/format/#fetching-filtering
        if (isIncludeNonFields === true && this.filters !== undefined && this.filters.length > 0) {
            let filtersResult = '';
            for (let filter of this.filters) {
                const params = filter.parameters;
                const curResult = params === undefined ?
                    `filter[${filter.field}][${filter.operation}]` :
                    `filter[${filter.field}][${filter.operation}]=${QueryBuilder.separateByComma(params)}`;
                filtersResult = filtersResult.length === 0 ? curResult : `${filtersResult}&${curResult}`;
            }
            params = params === null ? filtersResult : `${params}&${filtersResult}`;
        }
        // add sorts to get URL like '/articles?sort=-created,title'
        // see http://jsonapi.org/format/#fetching-sorting
        if (isIncludeNonFields === true && this.sorts !== undefined && this.sorts.length > 0) {
            let sortsList = '';
            for (let sort of this.sorts) {
                const sortParam = `${sort.isAscending === true ? '' : '-'}${sort.field}`;
                sortsList = sortsList.length > 0 ? `${sortsList},${sortParam}` : sortParam;
            }
            const sortsResult = `sort=${sortsList}`;
            params = params === null ? sortsResult : `${params}&${sortsResult}`;
        }
        // add includes to get URL like '/articles/1?include=author,comments.author'
        // see http://jsonapi.org/format/#fetching-includes
        if (isIncludeNonFields === true && this.includes !== undefined && this.includes.length > 0) {
            const includesResult = `include=${QueryBuilder.separateByComma(this.includes)}`;
            params = params === null ? includesResult : `${params}&${includesResult}`;
        }
        // add pagination to get URL like '/articles?page[offset]=50&page[limit]=25'
        // note: the spec do not strictly define pagination parameters
        if (isIncludeNonFields === true && this.offset !== undefined && this.limit !== undefined) {
            const paginationResult = `page[offset]=${this.offset}&page[limit]=${this.limit}`;
            params = params === null ? paginationResult : `${params}&${paginationResult}`;
        }
        return params === null ? '' : `?${params}`;
    }
    /**
     * @internal
     */
    static separateByComma(values) {
        return Array.isArray(values) === true ? values.join(',') : `${values}`;
    }
}
exports.QueryBuilder = QueryBuilder;
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiUXVlcnlCdWlsZGVyLmpzIiwic291cmNlUm9vdCI6Ii9ob21lL25lb21lcngvUHJvamVjdHMvbGltb25jZWxsby9mcmFtZXdvcmsvY29tcG9uZW50cy9Kc29uQXBpQ2xpZW50L2Rpc3QvIiwic291cmNlcyI6WyJKc29uQXBpQ2xpZW50L1F1ZXJ5QnVpbGRlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiO0FBQUE7Ozs7Ozs7Ozs7Ozs7O0dBY0c7O0FBVUg7SUFzQ0ksWUFBWSxJQUFrQjtRQUMxQixJQUFJLENBQUMsa0JBQWtCLEdBQUcsSUFBSSxDQUFDO1FBQy9CLElBQUksQ0FBQyxJQUFJLEdBQUcsSUFBSSxDQUFDO0lBQ3JCLENBQUM7SUFFTSxVQUFVLENBQUMsR0FBRyxNQUFpQztRQUNsRCxJQUFJLENBQUMsTUFBTSxHQUFHLE1BQU0sQ0FBQztRQUVyQixPQUFPLElBQUksQ0FBQztJQUNoQixDQUFDO0lBRU0sV0FBVyxDQUFDLEdBQUcsT0FBbUM7UUFDckQsSUFBSSxDQUFDLE9BQU8sR0FBRyxPQUFPLENBQUM7UUFFdkIsT0FBTyxJQUFJLENBQUM7SUFDaEIsQ0FBQztJQUVNLFNBQVMsQ0FBQyxHQUFHLEtBQStCO1FBQy9DLElBQUksQ0FBQyxLQUFLLEdBQUcsS0FBSyxDQUFDO1FBRW5CLE9BQU8sSUFBSSxDQUFDO0lBQ2hCLENBQUM7SUFFTSxZQUFZLENBQUMsR0FBRyxhQUFpQztRQUNwRCxJQUFJLENBQUMsUUFBUSxHQUFHLGFBQWEsQ0FBQztRQUU5QixPQUFPLElBQUksQ0FBQztJQUNoQixDQUFDO0lBRU0sY0FBYyxDQUFDLE1BQWMsRUFBRSxLQUFhO1FBQy9DLE1BQU0sR0FBRyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxFQUFFLElBQUksQ0FBQyxLQUFLLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQztRQUMxQyxLQUFLLEdBQUcsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLEVBQUUsSUFBSSxDQUFDLEtBQUssQ0FBQyxLQUFLLENBQUMs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Q0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFXLE1BQU0sRUFBRSxDQUFDO0lBQy9GLENBQUM7Q0FDSjtBQWpMRCxvQ0FpTEMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIENvcHlyaWdodCAyMDE1LTIwMTcgaW5mb0BuZW9tZXJ4LmNvbVxuICpcbiAqIExpY2Vuc2VkIHVuZGVyIHRoZSBBcGFjaGUgTGljZW5zZSwgVmVyc2lvbiAyLjAgKHRoZSBcIkxpY2Vuc2VcIik7XG4gKiB5b3UgbWF5IG5vdCB1c2UgdGhpcyBmaWxlIGV4Y2VwdCBpbiBjb21wbGlhbmNlIHdpdGggdGhlIExpY2Vuc2UuXG4gKiBZb3UgbWF5IG9idGFpbiBhIGNvcHkgb2YgdGhlIExpY2Vuc2UgYXRcbiAqXG4gKiBodHRwOi8vd3d3LmFwYWNoZS5vcmcvbGljZW5zZXMvTElDRU5TRS0yLjBcbiAqXG4gKiBVbmxlc3MgcmVxdWlyZWQgYnkgYXBwbGljYWJsZSBsYXcgb3IgYWdyZWVkIHRvIGluIHdyaXRpbmcsIHNvZnR3YXJlXG4gKiBkaXN0cmlidXRlZCB1bmRlciB0aGUgTGljZW5zZSBpcyBkaXN0cmlidXRlZCBvbiBhbiBcIkFTIElTXCIgQkFTSVMsXG4gKiBXSVRIT1VUIFdBUlJBTlRJRVMgT1IgQ09ORElUSU9OUyBPRiBBTlkgS0lORCwgZWl0aGVyIGV4cHJlc3Mgb3IgaW1wbGllZC5cbiAqIFNlZSB0aGUgTGljZW5zZSBmb3IgdGhlIHNwZWNpZmljIGxhbmd1YWdlIGdvdmVybmluZyBwZXJtaXNzaW9ucyBhbmRcbiAqIGxpbWl0YXRpb25zIHVuZGVyIHRoZSBMaWNlbnNlLlxuICovXG5cbmltcG9ydCB7IEZpZWxkUGFyYW1ldGVySW50ZXJmYWNlIH0gZnJvbSAnLi4vQ29udHJhY3RzL0pzb25BcGlDbGllbnQvRmllbGRQYXJhbWV0ZXJJbnRlcmZhY2UnO1xuaW1wb3J0IHsgRmlsdGVyUGFyYW1ldGVySW50ZXJmYWNlIH0gZnJvbSAnLi4vQ29udHJhY3RzL0pzb25BcGlDbGllbnQvRmlsdGVyUGFyYW1ldGVySW50ZXJmYWNlJztcbmltcG9ydCB7IFF1ZXJ5QnVpbGRlckludGVyZmFjZSB9IGZyb20gJy4uL0NvbnRyYWN0cy9Kc29uQXBpQ2xpZW50L1F1ZXJ5QnVpbGRlckludGVyZmFjZSc7XG5pbXBvcnQgeyBTb3J0UGFyYW1ldGVySW50ZXJmYWNlIH0gZnJvbSAnLi4vQ29udHJhY3RzL0pzb25BcGlDbGllbnQvU29ydFBhcmFtZXRlckludGVyZmFjZSc7XG5pbXBvcnQgeyBSZWxhdGlvbnNoaXBOYW1lIH0gZnJvbSAnLi4vQ29udHJhY3RzL0pzb25BcGkvUmVsYXRpb25zaGlwTmFtZSc7XG5pbXBvcnQgeyBSZXNvdXJjZUlkZW50aXR5IH0gZnJvbSAnLi4vQ29udHJhY3RzL0pzb25BcGkvUmVzb3VyY2VJZGVudGl0eSc7XG5pbXBvcnQgeyBSZXNvdXJjZVR5cGUgfSBmcm9tICcuLi9Db250cmFjdHMvSnNvbkFwaS9SZXNvdXJjZVR5cGUnO1xuXG5leHBvcnQgY2xhc3MgUXVlcnlCdWlsZGVyIGltcGxlbWVudHMgUXVlcnlCdWlsZGVySW50ZXJmYWNlIHtcbiAgICAvKipcbiAgICAgKiBAaW50ZXJuYWxcbiAgICAgKi9cbiAgICBwcml2YXRlIHR5cGU6IFJlc291cmNlVHlwZTtcblxuICAgIC8qKlxuICAgICAqIEBpbnRlcm5hbFxuICAgICAqL1xuICAgIHByaXZhdGUgZmllbGRzOiBGaWVsZFBhcmFtZXRlckludGVyZmFjZVtdIHwgdW5kZWZpbmVkO1xuXG4gICAgLyoqXG4gICAgICogQGludGVybmFsXG4gICAgICovXG4gICAgcHJpdmF0ZSBmaWx0ZXJzOiBGaWx0ZXJQYXJhbWV0ZXJJbnRlcmZhY2VbXSB8IHVuZGVmaW5lZDtcblxuICAgIC8qKlxuICAgICAqIEBpbnRlcm5hbFxuICAgICAqL1xuICAgIHByaXZhdGUgc29ydHM6IFNvcnRQYXJhbWV0ZXJJbnRlcmZhY2VbXSB8IHVuZGVmaW5lZDtcblxuICAgIC8qKlxuICAgICAqIEBpbnRlcm5hbFxuICAgICAqL1xuICAgIHByaXZhdGUgaW5jbHVkZXM6IFJlbGF0aW9uc2hpcE5hbWVbXSB8IHVuZGVmaW5lZDtcblxuICAgIC8qKlxuICAgICAqIEBpbnRlcm5hbFxuICAgICAqL1xuICAgIHByaXZhdGUgb2Zmc2V0OiBudW1iZXIgfCB1bmRlZmluZWQ7XG5cbiAgICAvKipcbiAgICAgKiBAaW50ZXJuYWxcbiAgICAgKi9cbiAgICBwcml2YXRlIGxpbWl0OiBudW1iZXIgfCB1bmRlZmluZWQ7XG5cbiAgICBwcml2YXRlIGlzRW5jb2RlVXJpRW5hYmxlZDogYm9vbGVhbjtcblxuICAgIGNvbnN0cnVjdG9yKHR5cGU6IFJlc291cmNlVHlwZSkge1xuICAgICAgICB0aGlzLmlzRW5jb2RlVXJpRW5hYmxlZCA9IHRydWU7XG4gICAgICAgIHRoaXMudHlwZSA9IHR5cGU7XG4gICAgfVxuXG4gICAgcHVibGljIG9ubHlGaWVsZHMoLi4uZmllbGRzOiBGaWVsZFBhcmFtZXRlckludGVyZmFjZVtdKTogUXVlcnlCdWlsZGVySW50ZXJmYWNlIHtcbiAgICAgICAgdGhpcy5maWVsZHMgPSBmaWVsZHM7XG5cbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfVxuXG4gICAgcHVibGljIHdpdGhGaWx0ZXJzKC4uLmZpbHRlcnM6IEZpbHRlclBhcmFtZXRlckludGVyZmFjZVtdKTogUXVlcnlCdWlsZGVySW50ZXJmYWNlIHtcbiAgICAgICAgdGhpcy5maWx0ZXJzID0gZmlsdGVycztcblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9XG5cbiAgICBwdWJsaWMgd2l0aFNvcnRzKC4uLnNvcnRzOiBTb3J0UGFyYW1ldGVySW50ZXJmYWNlW10pOiBRdWVyeUJ1aWxkZXJJbnRlcmZhY2Uge1xuICAgICAgICB0aGlzLnNvcnRzID0gc29ydHM7XG5cbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfVxuXG4gICAgcHVibGljIHdpdGhJbmNsdWRlcyguLi5yZWxhdGlvbnNoaXBzOiBSZWxhdGlvbnNoaXBOYW1lW10pOiBRdWVyeUJ1aWxkZXJJbnRlcmZhY2Uge1xuICAgICAgICB0aGlzLmluY2x1ZGVzID0gcmVsYXRpb25zaGlwcztcblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9XG5cbiAgICBwdWJsaWMgd2l0aFBhZ2luYXRpb24ob2Zmc2V0OiBudW1iZXIsIGxpbWl0OiBudW1iZXIpOiBRdWVyeUJ1aWxkZXJJbnRlcmZhY2Uge1xuICAgICAgICBvZmZzZXQgPSBNYXRoLm1heCgtMSwgTWF0aC5mbG9vcihvZmZzZXQpKTtcbiAgICAgICAgbGltaXQgPSBNYXRoLm1heCgwLCBNYXRoLmZsb29yKGxpbWl0KSk7XG5cbiAgICAgICAgaWYgKG9mZnNldCA+PSAwICYmIGxpbWl0ID4gMCkge1xuICAgICAgICAgICAgdGhpcy5vZmZzZXQgPSBvZmZzZXQ7XG4gICAgICAgICAgICB0aGlzLmxpbWl0ID0gbGltaXQ7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICB0aGlzLm9mZnNldCA9IHVuZGVmaW5lZDtcbiAgICAgICAgICAgIHRoaXMubGltaXQgPSB1bmRlZmluZWQ7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9XG5cbiAgICBwdWJsaWMgZW5hYmxlRW5jb2RlVXJpKCk6IFF1ZXJ5QnVpbGRlckludGVyZmFjZSB7XG4gICAgICAgIHRoaXMuaXNFbmNvZGVVcmlFbmFibGVkID0gdHJ1ZTtcblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9XG5cbiAgICBwdWJsaWMgZGlzYWJsZUVuY29kZVVyaSgpOiBRdWVyeUJ1aWxkZXJJbnRlcmZhY2Uge1xuICAgICAgICB0aGlzLmlzRW5jb2RlVXJpRW5hYmxlZCA9IGZhbHNlO1xuXG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH1cblxuICAgIHB1YmxpYyBpc1VyaUVuY29kaW5nRW5hYmxlZCgpOiBib29sZWFuIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuaXNFbmNvZGVVcmlFbmFibGVkO1xuICAgIH1cblxuICAgIHB1YmxpYyByZWFkKGluZGV4OiBSZXNvdXJjZUlkZW50aXR5LCByZWxhdGlvbnNoaXA/OiBSZWxhdGlvbnNoaXBOYW1lKTogc3RyaW5nIHtcbiAgICAgICAgY29uc3QgcmVsYXRpb25zaGlwVGFpbCA9IHJlbGF0aW9uc2hpcCA9PT0gdW5kZWZpbmVkID8gYC8ke2luZGV4fWAgOiBgLyR7aW5kZXh9LyR7cmVsYXRpb25zaGlwfWA7XG4gICAgICAgIGNvbnN0IHJlc3VsdCA9IGAvJHt0aGlzLnR5cGV9JHtyZWxhdGlvbnNoaXBUYWlsfSR7dGhpcy5idWlsZFBhcmFtZXRlcnMoZmFsc2UpfWA7XG5cbiAgICAgICAgcmV0dXJuIHRoaXMuaXNVcmlFbmNvZGluZ0VuYWJsZWQoKSA9PT0gdHJ1ZSA/IGVuY29kZVVSSShyZXN1bHQpIDogcmVzdWx0O1xuICAgIH1cblxuICAgIHB1YmxpYyBpbmRleCgpOiBzdHJpbmcge1xuICAgICAgICBjb25zdCByZXN1bHQgPSBgLyR7dGhpcy50eXBlfSR7dGhpcy5idWlsZFBhcmFtZXRlcnModHJ1ZSl9YDtcblxuICAgICAgICByZXR1cm4gdGhpcy5pc1VyaUVuY29kaW5nRW5hYmxlZCgpID09PSB0cnVlID8gZW5jb2RlVVJJKHJlc3VsdCkgOiByZXN1bHQ7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQGludGVybmFsXG4gICAgICovXG4gICAgcHJpdmF0ZSBidWlsZFBhcmFtZXRlcnMoaXNJbmNsdWRlTm9uRmllbGRzOiBib29sZWFuKTogc3RyaW5nIHtcbiAgICAgICAgbGV0IHBhcmFtcyA9IG51bGw7XG5cbiAgICAgICAgLy8gYWRkIGZpZWxkIHBhcmFtcyB0byBnZXQgVVJMIGxpa2UgJy9hcnRpY2xlcz9pbmNsdWRlPWF1dGhvciZmaWVsZHNbYXJ0aWNsZXNdPXRpdGxlLGJvZHkmZmllbGRzW3Blb3BsZV09bmFtZSdcbiAgICAgICAgLy8gc2VlIGh0dHA6Ly9qc29uYXBpLm9yZy9mb3JtYXQvI2ZldGNoaW5nLXNwYXJzZS1maWVsZHNldHNcbiAgICAgICAgaWYgKHRoaXMuZmllbGRzICE9PSB1bmRlZmluZWQgJiYgdGhpcy5maWVsZHMubGVuZ3RoID4gMCkge1xuICAgICAgICAgICAgbGV0IGZpZWxkc1Jlc3VsdCA9ICcnO1xuICAgICAgICAgICAgZm9yIChsZXQgZmllbGQgb2YgdGhpcy5maWVsZHMpIHtcbiAgICAgICAgICAgICAgICBjb25zdCBjdXJSZXN1bHQgPSBgZmllbGRzWyR7ZmllbGQudHlwZX1dPSR7UXVlcnlCdWlsZGVyLnNlcGFyYXRlQnlDb21tYShmaWVsZC5maWVsZHMpfWA7XG4gICAgICAgICAgICAgICAgZmllbGRzUmVzdWx0ID0gZmllbGRzUmVzdWx0Lmxlbmd0aCA9PT0gMCA/IGN1clJlc3VsdCA6IGAke2ZpZWxkc1Jlc3VsdH0mJHtjdXJSZXN1bHR9YDtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHBhcmFtcyA9IGZpZWxkc1Jlc3VsdDtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGFkZCBmaWx0ZXIgcGFyYW1ldGVycyB0byBnZXQgVVJMIGxpa2UgJ2ZpbHRlcltpZF1bZ3JlYXRlci10aGFuXT0xMCZmaWx0ZXJbaWRdW2xlc3MtdGhhbl09MjAmZmlsdGVyW3RpdGxlXVtsaWtlXT0lVHlwJSdcbiAgICAgICAgLy8gbm90ZTogdGhlIHNwZWMgZG8gbm90IHNwZWNpZnkgZm9ybWF0IGZvciBmaWx0ZXJzIGh0dHA6Ly9qc29uYXBpLm9yZy9mb3JtYXQvI2ZldGNoaW5nLWZpbHRlcmluZ1xuICAgICAgICBpZiAoaXNJbmNsdWRlTm9uRmllbGRzID09PSB0cnVlICYmIHRoaXMuZmlsdGVycyAhPT0gdW5kZWZpbmVkICYmIHRoaXMuZmlsdGVycy5sZW5ndGggPiAwKSB7XG4gICAgICAgICAgICBsZXQgZmlsdGVyc1Jlc3VsdCA9ICcnO1xuICAgICAgICAgICAgZm9yIChsZXQgZmlsdGVyIG9mIHRoaXMuZmlsdGVycykge1xuICAgICAgICAgICAgICAgIGNvbnN0IHBhcmFtcyA9IGZpbHRlci5wYXJhbWV0ZXJzO1xuICAgICAgICAgICAgICAgIGNvbnN0IGN1clJlc3VsdCA9IHBhcmFtcyA9PT0gdW5kZWZpbmVkID9cbiAgICAgICAgICAgICAgICAgICAgYGZpbHRlclske2ZpbHRlci5maWVsZH1dWyR7ZmlsdGVyLm9wZXJhdGlvbn1dYCA6XG4gICAgICAgICAgICAgICAgICAgIGBmaWx0ZXJbJHtmaWx0ZXIuZmllbGR9XVske2ZpbHRlci5vcGVyYXRpb259XT0ke1F1ZXJ5QnVpbGRlci5zZXBhcmF0ZUJ5Q29tbWEocGFyYW1zKX1gO1xuICAgICAgICAgICAgICAgIGZpbHRlcnNSZXN1bHQgPSBmaWx0ZXJzUmVzdWx0Lmxlbmd0aCA9PT0gMCA/IGN1clJlc3VsdCA6IGAke2ZpbHRlcnNSZXN1bHR9JiR7Y3VyUmVzdWx0fWA7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBwYXJhbXMgPSBwYXJhbXMgPT09IG51bGwgPyBmaWx0ZXJzUmVzdWx0IDogYCR7cGFyYW1zfSYke2ZpbHRlcnNSZXN1bHR9YDtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGFkZCBzb3J0cyB0byBnZXQgVVJMIGxpa2UgJy9hcnRpY2xlcz9zb3J0PS1jcmVhdGVkLHRpdGxlJ1xuICAgICAgICAvLyBzZWUgaHR0cDovL2pzb25hcGkub3JnL2Zvcm1hdC8jZmV0Y2hpbmctc29ydGluZ1xuICAgICAgICBpZiAoaXNJbmNsdWRlTm9uRmllbGRzID09PSB0cnVlICYmIHRoaXMuc29ydHMgIT09IHVuZGVmaW5lZCAmJiB0aGlzLnNvcnRzLmxlbmd0aCA+IDApIHtcbiAgICAgICAgICAgIGxldCBzb3J0c0xpc3QgPSAnJztcbiAgICAgICAgICAgIGZvciAobGV0IHNvcnQgb2YgdGhpcy5zb3J0cykge1xuICAgICAgICAgICAgICAgIGNvbnN0IHNvcnRQYXJhbSA9IGAke3NvcnQuaXNBc2NlbmRpbmcgPT09IHRydWUgPyAnJyA6ICctJ30ke3NvcnQuZmllbGR9YDtcbiAgICAgICAgICAgICAgICBzb3J0c0xpc3QgPSBzb3J0c0xpc3QubGVuZ3RoID4gMCA/IGAke3NvcnRzTGlzdH0sJHtzb3J0UGFyYW19YCA6IHNvcnRQYXJhbTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGNvbnN0IHNvcnRzUmVzdWx0ID0gYHNvcnQ9JHtzb3J0c0xpc3R9YDtcbiAgICAgICAgICAgIHBhcmFtcyA9IHBhcmFtcyA9PT0gbnVsbCA/IHNvcnRzUmVzdWx0IDogYCR7cGFyYW1zfSYke3NvcnRzUmVzdWx0fWA7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBhZGQgaW5jbHVkZXMgdG8gZ2V0IFVSTCBsaWtlICcvYXJ0aWNsZXMvMT9pbmNsdWRlPWF1dGhvcixjb21tZW50cy5hdXRob3InXG4gICAgICAgIC8vIHNlZSBodHRwOi8vanNvbmFwaS5vcmcvZm9ybWF0LyNmZXRjaGluZy1pbmNsdWRlc1xuICAgICAgICBpZiAoaXNJbmNsdWRlTm9uRmllbGRzID09PSB0cnVlICYmIHRoaXMuaW5jbHVkZXMgIT09IHVuZGVmaW5lZCAmJiB0aGlzLmluY2x1ZGVzLmxlbmd0aCA+IDApIHtcbiAgICAgICAgICAgIGNvbnN0IGluY2x1ZGVzUmVzdWx0ID0gYGluY2x1ZGU9JHtRdWVyeUJ1aWxkZXIuc2VwYXJhdGVCeUNvbW1hKHRoaXMuaW5jbHVkZXMpfWA7XG4gICAgICAgICAgICBwYXJhbXMgPSBwYXJhbXMgPT09IG51bGwgPyBpbmNsdWRlc1Jlc3VsdCA6IGAke3BhcmFtc30mJHtpbmNsdWRlc1Jlc3VsdH1gO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gYWRkIHBhZ2luYXRpb24gdG8gZ2V0IFVSTCBsaWtlICcvYXJ0aWNsZXM/cGFnZVtvZmZzZXRdPTUwJnBhZ2VbbGltaXRdPTI1J1xuICAgICAgICAvLyBub3RlOiB0aGUgc3BlYyBkbyBub3Qgc3RyaWN0bHkgZGVmaW5lIHBhZ2luYXRpb24gcGFyYW1ldGVyc1xuICAgICAgICBpZiAoaXNJbmNsdWRlTm9uRmllbGRzID09PSB0cnVlICYmIHRoaXMub2Zmc2V0ICE9PSB1bmRlZmluZWQgJiYgdGhpcy5saW1pdCAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICBjb25zdCBwYWdpbmF0aW9uUmVzdWx0ID0gYHBhZ2Vbb2Zmc2V0XT0ke3RoaXMub2Zmc2V0fSZwYWdlW2xpbWl0XT0ke3RoaXMubGltaXR9YDtcbiAgICAgICAgICAgIHBhcmFtcyA9IHBhcmFtcyA9PT0gbnVsbCA/IHBhZ2luYXRpb25SZXN1bHQgOiBgJHtwYXJhbXN9JiR7cGFnaW5hdGlvblJlc3VsdH1gO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHBhcmFtcyA9PT0gbnVsbCA/ICcnIDogYD8ke3BhcmFtc31gO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEBpbnRlcm5hbFxuICAgICAqL1xuICAgIHByaXZhdGUgc3RhdGljIHNlcGFyYXRlQnlDb21tYSh2YWx1ZXM6IHN0cmluZyB8IHN0cmluZ1tdKTogc3RyaW5nIHtcbiAgICAgICAgcmV0dXJuIEFycmF5LmlzQXJyYXkodmFsdWVzKSA9PT0gdHJ1ZSA/ICg8c3RyaW5nW10+dmFsdWVzKS5qb2luKCcsJykgOiBgJHs8c3RyaW5nPnZhbHVlc31gO1xuICAgIH1cbn1cbiJdfQ==