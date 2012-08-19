<?php
/**
 * StageBloc package version
 *
 * @category  Services
 * @package   Services_StageBloc
 * @author    Josh Holat <bumblebee@stagebloc.com>
 * @copyright 2012 Josh Holat <bumblebee@stagebloc.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://github.com/stagebloc/php-sb-connect
 *
 * This code was adapted from the SoundCloud API wrapper by Anton Lindqvist <anton@qvister.se>.
 *
 */
class Services_StageBloc_Version
{

    const MAJOR = 1;
    const MINOR = 0;
    const PATCH = 0;

    /**
     * Magic to string method
     *
     * @return string
     *
     * @access public
     */
    function __toString()
    {
        return implode('.', array(self::MAJOR, self::MINOR, self::PATCH));
    }

}
