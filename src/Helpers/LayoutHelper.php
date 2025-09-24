<?php

namespace AnourValar\LaravelAtom\Helpers;

class LayoutHelper
{
    /**
     * Get a menu for the user
     *
     * @param mixed $currentRoute
     * @param \Illuminate\Foundation\Auth\User $user
     * @param mixed $menu
     * @return array
     */
    public function getMenu($currentRoute, \Illuminate\Foundation\Auth\User $user, $menu): array
    {
        $currentRoute ??= \Request::route()->getAction('as');
        if (is_string($menu)) {
            $menu = config($menu);
        }
        ksort($menu);

        foreach ($menu as $key => &$value) {
            // title
            $value['title'] = trans($value['title']);

            // is_active (default)
            $value['is_active'] = false;

            if (isset($value['dropdown'])) {
                $value['dropdown'] = $this->getMenu($currentRoute, $user, $value['dropdown']);
                if ($value['dropdown']) {
                    // counter
                    if (isset($value['counter'])) {
                        $value['counter'] = $value['counter']();
                    } else {
                        $value['counter'] = [];
                    }

                    // is_active, counter
                    foreach ($value['dropdown'] as $item) {
                        if ($item['is_active']) {
                            $value['is_active'] = true;
                        }

                        if ($item['counter'] && ! $value['counter']) {
                            $value['counter'] = $item['counter'];
                        }
                    }
                } else {
                    unset($menu[$key]);
                }
            } else {
                // Filters
                if (! $user->can(implode('|', $value['user_ability']))) {
                    unset($menu[$key]);
                    continue;
                }
                if (! $this->configConditionsPasses($value['config_conditions'] ?? [])) {
                    unset($menu[$key]);
                    continue;
                }

                // counter
                if (isset($value['counter'])) {
                    $value['counter'] = $value['counter']();
                } else {
                    $value['counter'] = [];
                }

                // is_active, url
                $value['is_active'] = $currentRoute == $value['route'];
                $value['url'] = $this->getUrl($value);
            }

        }
        unset($value);

        return $menu;
    }

    /**
     * @param array $configConditions
     * @return bool
     */
    private function configConditionsPasses(array $configConditions): bool
    {
        foreach ($configConditions as $key => $value) {
            if (! in_array(config($key), (array) $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $item
     * @return string
     */
    private function getUrl(array $item): string
    {
        if (is_array($item['route'])) {
            $item['additional']['params'] = array_replace(($item['additional']['params'] ?? []), array_pop($item['route']));

            $item['route'] = array_shift($item['route']);
        }

        return route($item['route'], ($item['additional']['params'] ?? [])) . ($item['additional']['query'] ?? '');
    }
}
