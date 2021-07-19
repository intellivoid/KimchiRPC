<?php


    namespace KimchiRPC\Abstracts;

    /**
     * Class ServerMode
     * @package KimchiRPC\Abstracts
     */
    abstract class ServerMode
    {
        /**
         * Indicates that KimchiRPC is running in service mode, which acts like a supervisor and manages all the
         * executing workers in the background.
         */
        const Service = "SERVICE";

        /**
         * Indicates that KimchiRPC is running in worker mode, this simply uses BackgroundWorker and listens for
         * incoming jobs so that it can be fulfilled.
         */
        const Worker = "WORKER";

        /**
         * Indicates that KimchiRPC is running in handler mode, this is the main entry point for handling HTTP
         * requests and processing the jobs or pushing the jobs to the service supervisor.
         */
        const Handler = "HANDLER";
    }