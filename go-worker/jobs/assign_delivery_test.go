package jobs

import (
	"strings"
	"testing"

	"tootli.mx/worker/models"
)

func TestEvaluateEligibility_ApprovedActive(t *testing.T) {
	dm := models.DeliveryMan{
		Status:            1,
		ApplicationStatus: "approved",
		Active:            1,
		CurrentOrders:     0,
		DmTier:            "standard",
	}
	order := models.Order{
		PaymentMethod: "digital_payment",
		OrderAmount:   250.0,
	}
	limit := models.DmTierLimit{
		Tier:                "standard",
		MaxConcurrentOrders: 5,
	}

	eligible, reason := EvaluateEligibility(dm, order, limit, 500, 700, "assign_any", 0)
	if !eligible {
		t.Errorf("Expected driver to be eligible, but got false. Reason: %s", reason)
	}
}

func TestEvaluateEligibility_Suspended(t *testing.T) {
	dm := models.DeliveryMan{
		Status:            0, // Suspended!
		ApplicationStatus: "approved",
		Active:            1,
	}
	order := models.Order{}
	limit := models.DmTierLimit{Tier: "standard", MaxConcurrentOrders: 5}

	eligible, reason := EvaluateEligibility(dm, order, limit, 500, 700, "assign_any", 0)
	if eligible || !strings.Contains(reason, "suspended") {
		t.Errorf("Expected suspended driver to be ineligible, got eligible=%v, reason=%s", eligible, reason)
	}
}

func TestEvaluateEligibility_NotApproved(t *testing.T) {
	dm := models.DeliveryMan{
		Status:            1,
		ApplicationStatus: "pending", // Pending!
		Active:            1,
	}
	order := models.Order{}
	limit := models.DmTierLimit{Tier: "standard", MaxConcurrentOrders: 5}

	eligible, reason := EvaluateEligibility(dm, order, limit, 500, 700, "assign_any", 0)
	if eligible || !strings.Contains(reason, "not approved") {
		t.Errorf("Expected pending application driver to be ineligible, got eligible=%v, reason=%s", eligible, reason)
	}
}

func TestEvaluateEligibility_Offline(t *testing.T) {
	dm := models.DeliveryMan{
		Status:            1,
		ApplicationStatus: "approved",
		Active:            0, // Offline!
	}
	order := models.Order{}
	limit := models.DmTierLimit{Tier: "standard", MaxConcurrentOrders: 5}

	eligible, reason := EvaluateEligibility(dm, order, limit, 500, 700, "assign_any", 0)
	if eligible || !strings.Contains(reason, "offline") {
		t.Errorf("Expected offline driver to be ineligible, got eligible=%v, reason=%s", eligible, reason)
	}
}

func TestEvaluateEligibility_MaxConcurrentOrders(t *testing.T) {
	dm := models.DeliveryMan{
		Status:            1,
		ApplicationStatus: "approved",
		Active:            1,
		CurrentOrders:     2,
	}
	order := models.Order{}
	limit := models.DmTierLimit{
		Tier:                "new",
		MaxConcurrentOrders: 2, // Limit reached!
	}

	eligible, reason := EvaluateEligibility(dm, order, limit, 500, 700, "assign_any", 0)
	if eligible || !strings.Contains(reason, "max concurrent orders") {
		t.Errorf("Expected driver with max concurrent orders to be ineligible, got eligible=%v, reason=%s", eligible, reason)
	}
}

func TestEvaluateEligibility_MaxOrderValueCod(t *testing.T) {
	dm := models.DeliveryMan{
		Status:            1,
		ApplicationStatus: "approved",
		Active:            1,
		DmTier:            "restricted",
	}
	order := models.Order{
		PaymentMethod: "cash_on_delivery",
		OrderAmount:   450.0, // Exceeds 400 limit!
	}
	maxOrderVal := 400.0
	limit := models.DmTierLimit{
		Tier:                "restricted",
		MaxConcurrentOrders: 1,
		MaxOrderValueCod:    &maxOrderVal,
	}

	eligible, reason := EvaluateEligibility(dm, order, limit, 500, 700, "assign_any", 0)
	if eligible || !strings.Contains(reason, "exceeds max COD order value") {
		t.Errorf("Expected driver to exceed COD single value limit, got eligible=%v, reason=%s", eligible, reason)
	}
}

func TestEvaluateEligibility_CashLimitStrictStrategy(t *testing.T) {
	dm := models.DeliveryMan{
		Status:            1,
		ApplicationStatus: "approved",
		Active:            1,
		DmTier:            "standard",
	}
	order := models.Order{
		PaymentMethod: "cash_on_delivery",
		OrderAmount:   750.0, // High value order!
	}
	maxCashVal := 500.0
	limit := models.DmTierLimit{
		Tier:                "standard",
		MaxConcurrentOrders: 5,
		MaxCashCod:          &maxCashVal, // Limit 500
	}

	// 550 cash in hand >= 500 limit, strict strategy
	eligible, reason := EvaluateEligibility(dm, order, limit, 500, 700, "strict", 550)
	if eligible || !strings.Contains(reason, "cash limit exceeded") {
		t.Errorf("Expected driver to be ineligible due to cash limit, got eligible=%v, reason=%s", eligible, reason)
	}
}

func TestEvaluateEligibility_CashLimitRelaxationZone(t *testing.T) {
	dm := models.DeliveryMan{
		Status:            1,
		ApplicationStatus: "approved",
		Active:            1,
		DmTier:            "standard",
	}
	order := models.Order{
		PaymentMethod: "cash_on_delivery",
		OrderAmount:   350.0, // Under 700 high value threshold!
	}
	maxCashVal := 500.0
	limit := models.DmTierLimit{
		Tier:                "standard",
		MaxConcurrentOrders: 5,
		MaxCashCod:          &maxCashVal,
	}

	// 550 cash in hand >= 500 limit, but order is 350 (< 700), so allowed under relaxation zone
	eligible, reason := EvaluateEligibility(dm, order, limit, 500, 700, "strict", 550)
	if !eligible || !strings.Contains(reason, "Relaxation Zone") {
		t.Errorf("Expected driver to be allowed under Relaxation Zone, got eligible=%v, reason=%s", eligible, reason)
	}
}

func TestEvaluateEligibility_CashLimitRelaxedStrategy(t *testing.T) {
	dm := models.DeliveryMan{
		Status:            1,
		ApplicationStatus: "approved",
		Active:            1,
		DmTier:            "standard",
	}
	order := models.Order{
		PaymentMethod: "cash_on_delivery",
		OrderAmount:   800.0, // High value order!
	}
	maxCashVal := 500.0
	limit := models.DmTierLimit{
		Tier:                "standard",
		MaxConcurrentOrders: 5,
		MaxCashCod:          &maxCashVal,
	}

	// 550 cash in hand >= 500 limit, high value order, but strategy is relaxed
	eligible, reason := EvaluateEligibility(dm, order, limit, 500, 700, "relaxed_cash", 550)
	if !eligible || !strings.Contains(reason, "high value strategy") {
		t.Errorf("Expected driver to be allowed under high value relaxed strategy, got eligible=%v, reason=%s", eligible, reason)
	}
}
