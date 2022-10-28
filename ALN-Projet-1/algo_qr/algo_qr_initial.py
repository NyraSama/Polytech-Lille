import math
import numpy as np
import scipy.linalg as nla

def _VPAlgoQR_(M):
    # Algorithme QR
    # M est une matrice de données carrée
    A = [ M ]
    N = 20
    for k in range (1, N+1):
        Qk, Rk = nla.qr(A[k-1])
        Ak = np.dot(Rk, Qk)
        A.append(Ak)
    return np.diag(A[20])

# M = np.array([[1, 1, 0, 0], [1, 1, 1, 0], [0, 1, 2, 1], [0, 0, 1, 3]])