<?php

    interface IDateable {

        /**
         * Returns date in unixtime format, which object was created
         * @return int
         */
        function getDate();

    }