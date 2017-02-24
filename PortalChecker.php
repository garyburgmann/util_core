<?php

namespace go1\util;

use Doctrine\DBAL\Connection;

class PortalChecker
{
    public function load(Connection $db, $instance)
    {
        $column = is_numeric($instance) ? 'id' : 'title';

        return $db->executeQuery("SELECT * FROM gc_instance WHERE {$column} = ?", [$instance])->fetch(DB::OBJ);
    }

    public function isVirtual($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->is_virtual) ? true : (version_compare($portal->version, 'v3.0.0-alpha1') >= 0);
    }

    public function isLegacy($portal)
    {
        return !$this->isVirtual($portal);
    }

    public function getPrimaryDomain($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->primary_domain) ? $portal->configuration->primary_domain : $portal->title;
    }

    public function getSiteName($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->site_name) ? $portal->configuration->site_name : $portal->title;
    }

    public function isEnabled($portal)
    {
        return isset($portal->status) ? (PortalStatuses::ENABLED == $portal->status) : false;
    }

    public function getPublicKey($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->data->public_key) ? $portal->data->public_key : false;
    }

    public function canSendEmail($portal, $key)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->{$key}) ? $portal->configuration->{$key} : true;
    }

    public function allowPublicWriting($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->public_writing) ? $portal->configuration->public_writing : false;
    }

    public function allowSendingWelcomeEmail($portal)
    {
        PortalHelper::parseConfig($portal);

        if (!empty($portal->configuration)) {
            if (isset($portal->configuration->{PortalHelper::FEATURE_SEND_WELCOME_EMAIL})) {
                if (!$portal->configuration->{PortalHelper::FEATURE_SEND_WELCOME_EMAIL}) {
                    return false;
                }
            }
        }

        return PortalHelper::FEATURE_SEND_WELCOME_EMAIL_DEFAULT;
    }

    public function allowNotifyEnrolment($portal)
    {
        PortalHelper::parseConfig($portal);

        if (!empty($portal->configuration)) {
            if (isset($portal->configuration->{PortalHelper::FEATURE_NOTIFY_NEW_ENROLMENT})) {
                if (!$portal->configuration->{PortalHelper::FEATURE_NOTIFY_NEW_ENROLMENT}) {
                    return false;
                }
            }
        }

        return PortalHelper::FEATURE_NOTIFY_NEW_ENROLMENT_DEFAULT;
    }

    public function useCustomSMTP($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->configuration->{PortalHelper::FEATURE_CUSTOM_SMTP});
    }

    public function buildLink($portal, $uri)
    {
        $domain = $this->getPrimaryDomain($portal);
        $uri = ltrim($uri, '/');

        return ($this->isVirtual($portal))
            ? "https://{$domain}/p/#/{$uri}"
            : "https://{$domain}/webapp/#/{$uri}";
    }
}
