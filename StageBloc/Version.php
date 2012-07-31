<?php
/**
 * StageBloc package version
 *
 * @category  Services
 * @package   Services_StageBloc
 * @author    Anton Lindqvist <anton@qvister.se>
 * @copyright 2010 Anton Lindqvist <anton@qvister.se>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://github.com/mptre/php-soundcloud
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
