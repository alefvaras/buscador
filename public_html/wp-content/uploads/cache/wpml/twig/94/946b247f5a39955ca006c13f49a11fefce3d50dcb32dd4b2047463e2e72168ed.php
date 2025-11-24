<?php

namespace WPML\Core;

use \WPML\Core\Twig\Environment;
use \WPML\Core\Twig\Error\LoaderError;
use \WPML\Core\Twig\Error\RuntimeError;
use \WPML\Core\Twig\Markup;
use \WPML\Core\Twig\Sandbox\SecurityError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedTagError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFilterError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFunctionError;
use \WPML\Core\Twig\Source;
use \WPML\Core\Twig\Template;

/* currency-switcher-options-dialog.twig */
class __TwigTemplate_38ee77f3dab676e43a7959bd72d1c0e6b6d390a9cf10971213ff5852881beee7 extends \WPML\Core\Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<div class=\"wcml-dialog hidden\" id=\"wcml_currency_switcher_options_";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_switcher", []), "html", null, true);
        echo "\" title=\"";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "title", []), "html", null, true);
        echo "\">

    <div id=\"wcml_currency_switcher_options_form_";
        // line 3
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_switcher", []), "html", null, true);
        echo "\" class=\"wcml-currency-switcher-options-form\">

        <div id=\"wcml_curr_sel_preview_wrap\" class=\"wcml-currency-preview-wrapper wcml-currency-preview-diaglog\">
            <strong class=\"wcml-currency-preview-label\">";
        // line 6
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "preview", []), "html", null, true);
        echo "</strong>
            <input type=\"hidden\" id=\"wcml_currencies_switcher_preview_nonce\" value=\"";
        // line 7
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "preview_nonce", []), "html", null, true);
        echo "\"/>
            <div id=\"wcml_curr_sel_preview\" class=\"wcml-currency-preview ";
        // line 8
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_switcher", []), "html", null, true);
        echo "\">
                ";
        // line 9
        echo $this->getAttribute(($context["form"] ?? null), "switcher_preview", []);
        echo "
            </div>
        </div>

        <div id=\"wcml_curr_options_wrap\" class=\"wcml-currency-switcher-options\">
            ";
        // line 14
        if (($this->getAttribute(($context["args"] ?? null), "currency_switcher", []) == "new_widget")) {
            // line 15
            echo "
                    <h4>";
            // line 16
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "widgets", []), "widget_area", []), "html", null, true);
            echo "</h4>
                    <select id=\"wcml-cs-widget\">
                        <option selected disabled>";
            // line 18
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "widgets", []), "choose_label", []), "html", null, true);
            echo "</option>
                        ";
            // line 19
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute(($context["form"] ?? null), "widgets", []), "available_sidebars", []));
            foreach ($context['_seq'] as $context["_key"] => $context["sidebar"]) {
                // line 20
                echo "                            <option value=\"";
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["sidebar"], "id", [], "array"), "html", null, true);
                echo "\">";
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["sidebar"], "name", [], "array"), "html", null, true);
                echo "</option>
                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['sidebar'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 22
            echo "                    </select>

            ";
        }
        // line 25
        echo "
            <h4>";
        // line 26
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "switcher_style", []), "label", []), "html", null, true);
        echo "</h4>
            <ul class=\"wcml_curr_style\">
                <li>
                    <label>
                        <select id=\"currency_switcher_style\">
                            <optgroup label=\"";
        // line 31
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "switcher_style", []), "core", []), "html", null, true);
        echo "\">
                                ";
        // line 32
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute(($context["args"] ?? null), "switcher_templates", []), "core", [], "array"));
        foreach ($context['_seq'] as $context["switcher_template_id"] => $context["switcher_template"]) {
            // line 33
            echo "                                    <option value=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $context["switcher_template_id"], "html", null, true);
            echo "\" ";
            if (($this->getAttribute(($context["args"] ?? null), "switcher_style", []) == $context["switcher_template_id"])) {
                echo "selected=\"selected\"";
            }
            echo ">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["switcher_template"], "name", [], "array"), "html", null, true);
            echo "</option>
                                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['switcher_template_id'], $context['switcher_template'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 35
        echo "                            </optgroup>
                            <optgroup label=\"";
        // line 36
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "switcher_style", []), "custom", []), "html", null, true);
        echo "\">
                                ";
        // line 37
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute(($context["args"] ?? null), "switcher_templates", []), "custom", [], "array"));
        foreach ($context['_seq'] as $context["switcher_template_id"] => $context["switcher_template"]) {
            // line 38
            echo "                                    <option value=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $context["switcher_template_id"], "html", null, true);
            echo "\" ";
            if (($this->getAttribute(($context["args"] ?? null), "switcher_style", []) == $context["switcher_template_id"])) {
                echo "selected=\"selected\"";
            }
            echo ">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["switcher_template"], "name", [], "array"), "html", null, true);
            echo "</option>
                                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['switcher_template_id'], $context['switcher_template'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 40
        echo "                            </optgroup>
                        </select>
                    </label>
                </li>
            </ul>

            <h4>";
        // line 46
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "template", []), "label", []), "html", null, true);
        echo "</h4>
            <input type=\"text\" name=\"wcml_curr_template\" size=\"50\" value=\"";
        // line 47
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "template", []), "html", null, true);
        echo "\"/>
            <p class=\"explanation-text\">
                <span class=\"display-block\">";
        // line 49
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "template", []), "template_tip", []), "html", null, true);
        echo "</span>
                <span class=\"display-block\">";
        // line 50
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "template", []), "parameters", []), "html", null, true);
        echo ": ";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "template", []), "parameters_list", []), "html", null, true);
        echo "</span>
                <span class=\"display-block js-toggle-cs-style\" ";
        // line 51
        if (($this->getAttribute(($context["args"] ?? null), "style", []) != "list")) {
            echo "style=\"display: none;\"";
        }
        echo ">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "switcher_style", []), "allowed_tags", []), "html", null, true);
        echo "</span>
                <a href=\"#\" class=\"js-wcml-learn-more-button\">
                    <span class=\"js-wcml-learn-more-text\">";
        // line 53
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "template", []), "learn_more", []), "html", null, true);
        echo "</span>
                    <span class=\"js-wcml-hide-more-text\" style=\"display:none;\">";
        // line 54
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "template", []), "hide_more", []), "html", null, true);
        echo "</span>
                    <span class=\"otgs-ico-caret-down js-arrow-toggle\"></span>
                </a>
            </p>
            <input type=\"hidden\" id=\"currency_switcher_default\" value=\"";
        // line 58
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "template_default", []), "html", null, true);
        echo "\"/>
            <div class=\"js-wcml-learn-more-content learn-more-content-wrap hidden\">
                <p>";
        // line 60
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "template", []), "parameters_description", []), "html", null, true);
        echo "</p>
                <ul>
                    <li>";
        // line 62
        echo $this->getAttribute($this->getAttribute(($context["form"] ?? null), "template", []), "parameter_name", []);
        echo "</li>
                    <li>";
        // line 63
        echo $this->getAttribute($this->getAttribute(($context["form"] ?? null), "template", []), "parameter_symbol", []);
        echo "</li>
                    <li>";
        // line 64
        echo $this->getAttribute($this->getAttribute(($context["form"] ?? null), "template", []), "parameter_code", []);
        echo "</li>
                </ul>
                <p>";
        // line 66
        echo $this->getAttribute($this->getAttribute(($context["form"] ?? null), "template", []), "HTML_tags_available", []);
        echo "</p>
            </div>

            ";
        // line 69
        if (($this->getAttribute(($context["args"] ?? null), "currency_switcher", []) != "product")) {
            // line 70
            echo "                <h4>";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "widgets", []), "widget_title", []), "html", null, true);
            echo "</h4>
                <input type=\"text\" name=\"wcml_cs_widget_title\" size=\"50\" value=\"";
            // line 71
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "widget_title", []), "html", null, true);
            echo "\"/>
            ";
        }
        // line 73
        echo "
            <div class=\"js-wcml-cs-panel-colors wcml-cs-panel-colors\">
                <h4>";
        // line 75
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "colors", []), "label", []), "html", null, true);
        echo "</h4>

                <label for=\"wcml-cs-";
        // line 77
        echo \WPML\Core\twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "-colorpicker-preset\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "colors", []), "theme", []), "html", null, true);
        echo "</label>
                <select name=\"wcml-cs-";
        // line 78
        echo \WPML\Core\twig_escape_filter($this->env, ($context["id"] ?? null), "html", null, true);
        echo "-colorpicker-preset\" class=\"js-wcml-cs-colorpicker-preset\">
                    <option selected disabled>-- ";
        // line 79
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "colors", []), "select_option_choose", []), "html", null, true);
        echo " --</option>
                    ";
        // line 80
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["color_schemes"] ?? null));
        foreach ($context['_seq'] as $context["scheme_id"] => $context["scheme"]) {
            // line 81
            echo "                        <option value=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $context["scheme_id"], "html", null, true);
            echo "\">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["scheme"], "label", []), "html", null, true);
            echo "</option>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['scheme_id'], $context['scheme'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 83
        echo "                </select>

                <div>
                    <table>
                        <tr>
                            <td>
                            </td>
                            <th>";
        // line 90
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "colors", []), "normal", []), "html", null, true);
        echo "</th>
                            <th>";
        // line 91
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "colors", []), "hover", []), "html", null, true);
        echo "</th>
                        </tr>
                        ";
        // line 93
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["options"] ?? null));
        foreach ($context['_seq'] as $context["option_id"] => $context["option"]) {
            // line 94
            echo "                            <tr>
                                <td>";
            // line 95
            echo \WPML\Core\twig_escape_filter($this->env, $context["option"], "html", null, true);
            echo "</td>
                                ";
            // line 96
            if ( !(null === $this->getAttribute($this->getAttribute(($context["args"] ?? null), "options", []), ($context["option_id"] . "_normal"), [], "array"))) {
                // line 97
                echo "                                    <td class=\"js-wcml-cs-colorpicker-wrapper\">
                                        <input class=\"js-wcml-cs-colorpicker js-wcml-cs-color-";
                // line 98
                echo \WPML\Core\twig_escape_filter($this->env, $context["option_id"], "html", null, true);
                echo "_normal\" type=\"text\" size=\"7\"
                                               id=\"wcml-cs-";
                // line 99
                echo \WPML\Core\twig_escape_filter($this->env, $context["option_id"], "html", null, true);
                echo "-normal\" name=\"";
                echo \WPML\Core\twig_escape_filter($this->env, $context["option_id"], "html", null, true);
                echo "_normal\"
                                               value=\"";
                // line 100
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["args"] ?? null), "options", []), ($context["option_id"] . "_normal"), [], "array"), "html", null, true);
                echo "\" style=\"\">
                                    </td>
                                ";
            }
            // line 103
            echo "                                ";
            if ( !(null === $this->getAttribute($this->getAttribute(($context["args"] ?? null), "options", []), ($context["option_id"] . "_hover"), [], "array"))) {
                // line 104
                echo "                                    <td class=\"js-wcml-cs-colorpicker-wrapper\">
                                        <input class=\"js-wcml-cs-colorpicker js-wcml-cs-color-";
                // line 105
                echo \WPML\Core\twig_escape_filter($this->env, $context["option_id"], "html", null, true);
                echo "_hover\" type=\"text\" size=\"7\"
                                               id=\"wcml-cs-";
                // line 106
                echo \WPML\Core\twig_escape_filter($this->env, $context["option_id"], "html", null, true);
                echo "-hover\" name=\"";
                echo \WPML\Core\twig_escape_filter($this->env, $context["option_id"], "html", null, true);
                echo "_hover\"
                                               value=\"";
                // line 107
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["args"] ?? null), "options", []), ($context["option_id"] . "_hover"), [], "array"), "html", null, true);
                echo "\" style=\"\">
                                    </td>
                                ";
            }
            // line 110
            echo "                            </tr>
                        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['option_id'], $context['option'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 112
        echo "                    </table>

                </div>
            </div>
        </div>

    </div>
        <footer class=\"wpml-dialog-footer\">
            <input type=\"button\" class=\"cancel wcml-dialog-close-button wpml-dialog-close-button alignleft\" value=\"";
        // line 120
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "cancel", []), "html", null, true);
        echo "\"/>&nbsp;
            <input type=\"submit\" class=\"wcml-dialog-close-button wpml-dialog-close-button button-primary currency_switcher_save alignright\"
                   value=\"";
        // line 122
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "save", []), "html", null, true);
        echo "\" data-switcher=\"";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "current_switcher", []), "html", null, true);
        echo "\" data-stay=\"1\" />
            <input type=\"hidden\" id=\"wcml_currencies_switcher_save_settings_nonce\" value=\"";
        // line 123
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "save_settings_nonce", []), "html", null, true);
        echo "\"/>
            <input type=\"hidden\" id=\"wcml_currencies_switcher_id\" value=\"";
        // line 124
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_switcher", []), "html", null, true);
        echo "\"/>
        </footer>

    </div>

";
    }

    public function getTemplateName()
    {
        return "currency-switcher-options-dialog.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  390 => 124,  386 => 123,  380 => 122,  375 => 120,  365 => 112,  358 => 110,  352 => 107,  346 => 106,  342 => 105,  339 => 104,  336 => 103,  330 => 100,  324 => 99,  320 => 98,  317 => 97,  315 => 96,  311 => 95,  308 => 94,  304 => 93,  299 => 91,  295 => 90,  286 => 83,  275 => 81,  271 => 80,  267 => 79,  263 => 78,  257 => 77,  252 => 75,  248 => 73,  243 => 71,  238 => 70,  236 => 69,  230 => 66,  225 => 64,  221 => 63,  217 => 62,  212 => 60,  207 => 58,  200 => 54,  196 => 53,  187 => 51,  181 => 50,  177 => 49,  172 => 47,  168 => 46,  160 => 40,  145 => 38,  141 => 37,  137 => 36,  134 => 35,  119 => 33,  115 => 32,  111 => 31,  103 => 26,  100 => 25,  95 => 22,  84 => 20,  80 => 19,  76 => 18,  71 => 16,  68 => 15,  66 => 14,  58 => 9,  54 => 8,  50 => 7,  46 => 6,  40 => 3,  32 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "currency-switcher-options-dialog.twig", "/home/u888022333/domains/akibara.cl/public_html/wp-content/plugins/woocommerce-multilingual/templates/multi-currency/currency-switcher-options-dialog.twig");
    }
}
