<?php

namespace joole\framework\http\request;

/**
 * Allowed mutations.
 */
enum Mutations: string
{

    /** POST-data mutation */
    case POST_MUTATION = '_post';
    /** GET-data mutation */
    case GET_MUTATION = '_get';

}